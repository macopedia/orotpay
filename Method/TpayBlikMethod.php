<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method;

use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\NotificationVerifierInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Macopedia\Bundle\TpayBundle\Tpay\Response\ResponseFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayChannelIdInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayChannelProviderInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

use function count;

class TpayBlikMethod extends AbstractTpayMethod implements TpayChannelIdInterface
{
    protected const int CHANNEL_ID = 64;
    protected const string METHOD_SUFFIX = 'blik';

    public function __construct(
        protected PayloadFactoryInterface $payloadFactory,
        protected GatewayFactoryInterface $gatewayFactory,
        protected ResponseFactoryInterface $responseFactory,
        protected LoggerInterface $logger,
        protected NotificationVerifierInterface $notificationVerifierFactory,
        protected RouterInterface $router,
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
            $payloadProcessor = $this->payloadFactory->createFrom(PaymentMethod::BLIK);

            $payload = $payloadProcessor->process($paymentTransaction);
            $sanitizedPayload = $payloadProcessor->sanitize($payload);
            $paymentTransaction->setRequest($sanitizedPayload);

            $paymentResponse = $this->responseFactory->createFrom(
                $this->getTransactionsGateway()?->createTransaction($payload) ?? [],
                static function (array $response): bool {
                    if (($response['result'] ?? '') === 'failed' || count($response['payments']['errors'] ?? [])) {
                        return false;
                    }

                    return ($response['result'] ?? '') === 'success';
                }
            );

            if (!$paymentResponse->isSuccessful()) {
                $this->markPaymentTransactionAsFailed($paymentTransaction, $sanitizedPayload, $paymentResponse, 'payment');
                return ['successful' => false];
            }

            $this->enrichPaymentTransactionByResponse($paymentTransaction, $paymentResponse);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $paymentResponse = $this->responseFactory->createEmpty();

            $this->markPaymentTransactionAsFailed($paymentTransaction, $sanitizedPayload ?? [], $paymentResponse, 'payment');
            $paymentTransaction->addWebhookRequestLog(['message' => $e->getMessage(), 'exception' => $e]);
        }

        return [
            'successful' => $paymentResponse->isSuccessful(),
            'statusUrl' => $this->router->generate('tpay_payment_status', ['accessIdentifier' => $paymentTransaction->getAccessIdentifier()]),
        ];
    }

    public function getChannelId(): int
    {
        return self::CHANNEL_ID;
    }
}
