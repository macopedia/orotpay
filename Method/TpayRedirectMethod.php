<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\NotificationVerifierInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\TransactionPayloadInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Response\ResponseFactoryInterface;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function get_class;

class TpayRedirectMethod extends AbstractTpayMethod
{
    protected const string METHOD_SUFFIX = 'redirect';

    public function __construct(
        //        protected TpayConfigInterface $config,
        protected TransactionPayloadInterface $transactionPayload,
        protected GatewayFactoryInterface $gatewayFactory,
        protected ResponseFactoryInterface $responseFactory,
        protected LoggerInterface $logger,
        protected NotificationVerifierInterface $notificationVerifierFactory,
    ) {
    }

    public function isApplicable(PaymentContextInterface $context): bool
    {
        if (!$this->config->isHiddenInCheckout()) {
            return parent::isApplicable($context);
        }

        $isNotCheckout = get_class($context->getSourceEntity()) !== Checkout::class;

        return parent::isApplicable($context) && $isNotCheckout;
    }

    protected function purchase(PaymentTransaction $paymentTransaction): array
    {
        $paymentTransaction->setAction(PaymentMethodInterface::PENDING);
        $paymentTransaction->setActive(true);

        try {
            $gateway = $this->getTransactionsGateway();

            $transactionPayload = $this->transactionPayload->process($paymentTransaction);
            $transactionResponse = $this->responseFactory->createFrom($gateway->createTransaction($transactionPayload));
            $paymentTransaction->setRequest($transactionPayload);

            if (!$transactionResponse->isSuccessful()) {
                $this->markPaymentTransactionAsFailed($paymentTransaction, $transactionPayload, $transactionResponse);

                return ['successful' => false];
            }

            $this->enrichPaymentTransactionByResponse($paymentTransaction, $transactionResponse);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $transactionResponse = $this->responseFactory->createEmpty();

            $this->markPaymentTransactionAsFailed($paymentTransaction, $transactionPayload ?? [], $transactionResponse);

            $paymentTransaction->addWebhookRequestLog(['message' => $e->getMessage(), 'exception' => $e]);
        }

        return [
            'successful' => $transactionResponse->isSuccessful(),
            'paymentUrl' => $transactionResponse->getPaymentUrl()
        ];
    }
}
