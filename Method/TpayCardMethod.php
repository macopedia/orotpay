<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method;

use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\NotificationVerifierInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PayGroup;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\TransactionPayloadInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Macopedia\Bundle\TpayBundle\Tpay\Response\ResponseFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\SavedCardInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayChannelIdInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayChannelProviderInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

class TpayCardMethod extends AbstractTpayMethod implements TpayChannelIdInterface
{
    public const string SAVED_CARD = 'saved_card';
    protected const int CHANNEL_ID = 53;
    protected const string METHOD_SUFFIX = 'card';

    public function __construct(
        protected TransactionPayloadInterface $transactionPayload,
        protected PayloadFactoryInterface $payloadFactory,
        protected GatewayFactoryInterface $gatewayFactory,
        protected ResponseFactoryInterface $responseFactory,
        protected LoggerInterface $logger,
        protected RequestStack $requestStack,
        protected NotificationVerifierInterface $notificationVerifierFactory,
        protected SavedCardInterface $savedCardHandler,
        protected TpayChannelProviderInterface $channelProvider
    ) {
    }

    public function isApplicable(PaymentContextInterface $context): bool
    {
        return parent::isApplicable($context) &&
            $this->channelProvider->isChannelApplicable(
                $this->config,
                self::CHANNEL_ID,
                $context
            );
    }

    protected function purchase(PaymentTransaction $paymentTransaction): array
    {
        $paymentTransaction->setAction(PaymentMethodInterface::PENDING);
        $paymentTransaction->setActive(true);

        try {
            $gateway = $this->getTransactionsGateway();

            $transactionPayload = $this->transactionPayload->process($paymentTransaction);
            $transactionPayload['pay']['groupId'] = PayGroup::CARD->value;

            $transactionResponse = $this->responseFactory->createFrom($gateway->createTransaction($transactionPayload));

            if (!$transactionResponse->isSuccessful()) {
                $this->markPaymentTransactionAsFailed($paymentTransaction, $transactionPayload, $transactionResponse);

                return ['successful' => false];
            }

            $payloadProcessor = $this->payloadFactory->createFrom(PaymentMethod::CARD);
            $payload = $payloadProcessor->process($paymentTransaction);
            $sanitizedPayload = $payloadProcessor->sanitize($payload);
            $paymentTransaction->setRequest(['transaction' => $transactionPayload, 'payment' => $sanitizedPayload]);

            $paymentResponse = $this->responseFactory->createFrom(
                $gateway->createPaymentByTransactionId(
                    $payload,
                    $transactionResponse->getTransactionId()
                )
            );

            if (!$paymentResponse->isSuccessful()) {
                $this->markPaymentTransactionAsFailed($paymentTransaction, $sanitizedPayload, $paymentResponse);

                return ['successful' => false];
            }

            if ($paymentResponse->isCorrect()) {
                $paymentTransaction->setActive(false);
                $paymentTransaction->setAction(PaymentMethodInterface::PURCHASE);
            }

            $this->enrichPaymentTransactionByResponse($paymentTransaction, $paymentResponse);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $paymentResponse = $this->responseFactory->createEmpty();

            $this->markPaymentTransactionAsFailed($paymentTransaction, [], $paymentResponse);
            $paymentTransaction->addWebhookRequestLog(['message' => $e->getMessage(), 'exception' => $e]);
        }

        return [
            'successful' => $paymentResponse->isSuccessful(),
            'paymentUrl' => $paymentResponse->shouldRedirectFor3ds() ? $paymentResponse->getPaymentUrl() : null,
        ];
    }

    protected function complete(PaymentTransaction $paymentTransaction): array
    {
        parent::complete($paymentTransaction);

        $request = $this->requestStack->getCurrentRequest();
        $cardToken = $request?->request->get('card_token', '');
        $cardBrand = $request?->request->get('card_brand', '');
        $cardTail = $request?->request->get('card_tail', '');
        $cardExpirationDate = $request?->request->get('token_expiry_date');

        if ($cardToken === '' || $cardBrand === '' || $cardTail === '') {
            return [];
        }

        $cardData = [
            'token' => $cardToken,
            'brand' => $cardBrand,
            'tail' => $cardTail,
            'expirationDate' => $cardExpirationDate,
        ];

        $newCardToSave = $this->savedCardHandler->createIfNotExist(
            $this->savedCardHandler->getHash($cardData),
            $paymentTransaction,
        );

        if ($newCardToSave === null) {
            return [];
        }

        $newCardToSave->setResponse($cardData);
        $newCardToSave->setSourcePaymentTransaction($paymentTransaction);
        $this->savedCardHandler->save($newCardToSave);

        return [];
    }

    public function getChannelId(): int
    {
        return self::CHANNEL_ID;
    }
}
