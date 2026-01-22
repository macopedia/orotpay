<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Provider;

use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class TransactionProvider
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected PaymentTransactionRepository $paymentTransactionRepository,
        protected int $expirationDays = 7
    ) {
    }

    public function getAmountToRefund(PaymentTransaction $sourceTransaction): float
    {
        $transactions = $this->paymentTransactionRepository->findSuccessfulRelatedTransactionsByAction(
            $sourceTransaction,
            PaymentMethodInterface::REFUND
        );

        $amount = 0.00;
        if ($transactions !== []) {
            $refundedAmounts = array_map(
                fn (PaymentTransaction $transaction) => $this->formatAmount(
                    (float)$transaction->getAmount()
                ),
                $transactions
            );
            $amount = array_sum($refundedAmounts);
        }

        return $this->formatAmount($sourceTransaction->getAmount() - $amount);
    }

    public function getTransactionsToCancel(array $paymentMethods): ?BufferedIdentityQueryResultIterator
    {
        $queryBuilder = $this->getBuilder($paymentMethods);

        return new BufferedIdentityQueryResultIterator($queryBuilder);
    }

    public function hasAnyTransactions(array $paymentMethods): bool
    {
        $queryBuilder = $this->getBuilder($paymentMethods);

        $queryBuilder->resetDQLPart('select');
        $queryBuilder->select('count(pt.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param array<string> $paymentMethods
     */
    private function getBuilder(array $paymentMethods): QueryBuilder
    {
        $repository = $this->managerRegistry->getRepository(PaymentTransaction::class);
        $queryBuilder = $repository->createQueryBuilder('pt');
        $queryBuilder
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('pt.action', ':action'),
                    $queryBuilder->expr()->lte('pt.createdAt', ':createdAt'),
                    $queryBuilder->expr()->eq('pt.successful', ':isSuccessful'),
                    $queryBuilder->expr()->eq('pt.active', ':isActive'),
                    $queryBuilder->expr()->in('pt.paymentMethod', ':paymentMethods'),
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('action', PaymentMethodInterface::PENDING),
                new Parameter('createdAt', new DateTime(
                    sprintf('- %d days', $this->expirationDays),
                    new DateTimeZone('UTC')
                )),
                new Parameter('isSuccessful', true),
                new Parameter('isActive', false),
                new Parameter('paymentMethods', $paymentMethods)
            ]));

        return $queryBuilder;
    }

    private function formatAmount(float $amount): float
    {
        return round($amount, 2);
    }
}
