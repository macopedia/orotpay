<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method;

use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\NotificationVerifierInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\TransactionPayloadInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Macopedia\Bundle\TpayBundle\Tpay\Response\ResponseFactoryInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TpayPayByLinkMethod extends AbstractTpayMethod
{
    protected const string METHOD_SUFFIX = 'pbl';

    public function __construct(
        protected TransactionPayloadInterface $transactionPayload,
        protected PayloadFactoryInterface $payloadFactory,
        protected GatewayFactoryInterface $gatewayFactory,
        protected ResponseFactoryInterface $responseFactory,
        protected LoggerInterface $logger,
        protected NotificationVerifierInterface $notificationVerifierFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    protected function purchase(PaymentTransaction $paymentTransaction): array
    {
        $paymentTransaction->setAction(PaymentMethodInterface::PENDING);
        $paymentTransaction->setActive(true);

        try {
            $payload = $this->payloadFactory->createFrom(PaymentMethod::PAY_BY_LINK)->process($paymentTransaction);
            $paymentTransaction->setRequest($payload);
            $paymentResponse = $this->responseFactory->createFrom($this->getTransactionsGateway()?->createTransaction($payload) ?? []);

            if (!$paymentResponse->isSuccessful()) {
                $this->markPaymentTransactionAsFailed($paymentTransaction, $payload, $paymentResponse, 'payment');
                return ['successful' => false];
            }

            $this->enrichPaymentTransactionByResponse($paymentTransaction, $paymentResponse);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $paymentResponse = $this->responseFactory->createEmpty();
            $this->markPaymentTransactionAsFailed($paymentTransaction, $payload ?? [], $paymentResponse, 'payment');
            $paymentTransaction->addWebhookRequestLog(['message' => $e->getMessage(), 'exception' => $e]);
        }

        return [
            'successful' => $paymentResponse->isSuccessful(),
            'paymentUrl' => $paymentResponse->getPaymentUrl()
        ];
    }
}
