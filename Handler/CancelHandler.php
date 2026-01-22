<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Handler;

use Macopedia\Bundle\TpayBundle\Method\Config\Provider\TpayConfigProviderInterface;
use Macopedia\Bundle\TpayBundle\Provider\TransactionProvider;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;
use Psr\Log\LoggerInterface;
use Throwable;

use function array_keys;

class CancelHandler implements CancelHandlerInterface
{
    public function __construct(
        protected PaymentTransactionProvider $paymentTransactionProvider,
        protected PaymentMethodProviderInterface $paymentMethodProvider,
        protected TransactionProvider $pendingTransactionProvider,
        protected TpayConfigProviderInterface $tpayConfigProvider,
        protected LoggerInterface $logger
    ) {
    }

    public function hasAnyTransactionsToCancel(): bool
    {
        $paymentConfigs = $this->getPaymentConfigurations();

        if ($paymentConfigs === []) {
            return false;
        }

        return $this->pendingTransactionProvider->hasAnyTransactions($paymentConfigs);
    }

    public function process(): void
    {
        $paymentConfigs = $this->getPaymentConfigurations();

        if ($paymentConfigs === []) {
            return;
        }

        $paymentMethods = [];
        $transactions = $this->pendingTransactionProvider->getTransactionsToCancel($paymentConfigs);

        foreach ($transactions as $transaction) {
            $paymentMethod = $this->getPaymentMethod($paymentMethods, $transaction);

            $this->cancelTransaction($transaction, $paymentMethod);
        }
    }

    protected function getPaymentMethod(array $paymentMethods, PaymentTransaction $transaction): PaymentMethodInterface
    {
        $id = $transaction->getPaymentMethod();

        if (!isset($paymentMethods[$id])) {
            $paymentMethods[$id] = $this->paymentMethodProvider->getPaymentMethod($id);
        }

        return $paymentMethods[$id];
    }

    protected function createCancelTransaction(PaymentTransaction $transaction): PaymentTransaction
    {
        $cancelTransaction = $this->paymentTransactionProvider->createPaymentTransactionByParentTransaction(
            PaymentMethodInterface::CANCEL,
            $transaction
        );

        $cancelTransaction->setReference($transaction->getReference());
        $cancelTransaction->addTransactionOption('reason', 'abandoned');

        return $cancelTransaction;
    }

    private function cancelTransaction(PaymentTransaction $pendingTransaction, PaymentMethodInterface $paymentMethod): void
    {
        try {
            $cancelTransaction = $this->createCancelTransaction($pendingTransaction);
            $cancelResponse = $paymentMethod->execute(PaymentMethodInterface::CANCEL, $cancelTransaction);

            $pendingTransaction->setActive(false);
            $this->paymentTransactionProvider->savePaymentTransaction($pendingTransaction);

            if (!$cancelTransaction->isSuccessful()) {
                $this->logger->warning($cancelResponse['error'] ?? 'Failed to cancel transaction', [
                    'transaction' => $cancelTransaction,
                    'response' => $cancelResponse,
                ]);

                $pendingTransaction->addWebhookRequestLog(['cancel_request' => $cancelResponse['error'] ?? 'Failed to cancel transaction']);
                $this->paymentTransactionProvider->savePaymentTransaction($pendingTransaction);

                return;
            }

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            try {
                $pendingTransaction->addWebhookRequestLog(['cancel_request' => $e->getMessage()]);
                $this->paymentTransactionProvider->savePaymentTransaction($pendingTransaction);
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }
        }
    }

    protected function getPaymentConfigurations(): array
    {
        $paymentConfigs = $this->tpayConfigProvider->getPaymentConfigs();

        if ($paymentConfigs === []) {
            return [];
        }

        return array_keys($paymentConfigs);
    }
}
