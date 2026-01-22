<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Macopedia\Bundle\TpayBundle\Tpay\Response\ResponseInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use RuntimeException;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;
use Tpay\OpenApi\Utilities\TpayException;

use function array_merge;
use function in_array;
use function json_encode;
use function round;

abstract class AbstractTpayMethod implements PaymentMethodInterface
{
    public const string COMPLETE = 'complete';
    public const string FAILED = 'failed';
    protected const int AMOUNT_PRECISION = 2;
    protected const METHOD_SUFFIX = '';

    protected TpayConfigInterface $config;

    public function setConfig(TpayConfigInterface $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction): array
    {
        return $this->{$action}($paymentTransaction) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return static::buildIdentifier($this->config->getPaymentMethodIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(PaymentContextInterface $context): bool
    {
        $amount = round($context->getTotal(), self::AMOUNT_PRECISION);
        $zeroAmount = round(0, self::AMOUNT_PRECISION);

        return !($amount === $zeroAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionName): bool
    {
        return in_array($actionName, [self::PURCHASE, self::COMPLETE, self::REFUND, self::CANCEL], true);
    }

    protected function enrichPaymentTransactionByResponse(
        PaymentTransaction $paymentTransaction,
        ResponseInterface $response
    ): void {
        $paymentTransaction->setResponse($response->getData());
        $paymentTransaction->setReference($response->getTransactionId());

        if ($response->isSuccessful()) {
            $paymentTransaction->setSuccessful(true);
            $paymentTransaction->setTransactionOptions(
                array_merge($paymentTransaction->getTransactionOptions(), [
                    ResponseInterface::TRANSACTION_STATUS => $response->getTransactionStatus(),
                    ResponseInterface::PAYMENT_URL => $response->getPaymentUrl(),
                ])
            );
        } else {
            $paymentTransaction->setAction(self::FAILED);
            $paymentTransaction->setActive(false);
        }
    }

    /**
     * @return TransactionsApi
     */
    protected function getTransactionsGateway(): object
    {
        return $this->gatewayFactory->create($this->config)->transactions();
    }

    protected function createJWSVerifiedPaymentNotification(): BasicPayment
    {
        return $this->notificationVerifierFactory->create($this->config);
    }

    protected function refund(PaymentTransaction $paymentTransaction): array
    {
        $paymentTransaction->setActive(false);
        $paymentTransaction->setSuccessful(false);
        $paymentTransaction->setAction(PaymentMethodInterface::REFUND);
        $paymentTransaction->setRequest(['refund' => true]);

        $result = $this->gatewayFactory->create($this->config)
            ->transactions()
            ->createRefundByTransactionId([], $paymentTransaction->getSourcePaymentTransaction()?->getReference());

        $paymentTransaction->addResponseLog(['refund' => $result]);

        if ($result['result'] !== 'success') {
            throw new TpayException('Error refund, errors: '. json_encode($result['errors'] ?? []));
        }

        $paymentTransaction->setSuccessful(true);

        return ['successful' => true];
    }

    /*
     * @return array{}
     */
    protected function complete(PaymentTransaction $paymentTransaction): array
    {
        $notification = $this->createJWSVerifiedPaymentNotification();
        $status = $notification->tr_status->getValue();
        $action = match (true) {
            str_contains($status, 'TRUE') => self::PURCHASE,
            str_contains($status, 'CHARGEBACK') => self::REFUND,
            default => self::FAILED,
        };

        $paymentTransaction->setAction($action);
        $paymentTransaction->setActive(false);
        $paymentTransaction->setSuccessful($action !== self::FAILED);

        return [];
    }

    protected function markPaymentTransactionAsFailed(
        PaymentTransaction $paymentTransaction,
        array $payloadSent,
        ResponseInterface $response,
        string $key = 'transaction'
    ): void {
        $paymentTransaction->setAction(self::FAILED);
        $paymentTransaction->setRequest([$key => $payloadSent]);
        $paymentTransaction->setSuccessful(false);
        $paymentTransaction->setActive(false);
        $paymentTransaction->setResponse($response->getData());
    }

    /*
     * @return array{}
     */
    protected function cancel(PaymentTransaction $paymentTransaction): array
    {
        $transactionId = $paymentTransaction->getReference();

        if ($transactionId === '') {
            $transactionId = $paymentTransaction->getSourcePaymentTransaction()?->getReference();
        }

        if ($transactionId === '') {
            throw new RuntimeException('Failure to cancel, missing transactionId');
        }

        $result = $this->gatewayFactory->create($this->config)
            ->transactions()
            ->cancelTransaction($transactionId);

        if ($result['result'] === 'success') {
            return ['successful' => true];
        }

        throw new RuntimeException('Failure to cancel, errors: '. json_encode($result['errors'] ?? []));
    }

    public static function buildIdentifier(string $identifier): string
    {
        return $identifier. '_'. static::METHOD_SUFFIX;
    }
}
