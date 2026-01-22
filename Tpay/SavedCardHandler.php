<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Doctrine\Common\Collections\Criteria;
use Macopedia\Bundle\TpayBundle\Method\TpayCardMethod;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;

use function array_values;
use function hash_equals;

readonly class SavedCardHandler implements SavedCardInterface
{
    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected CustomerUserProvider $customerUserProvider,
        protected PaymentTransactionProvider $paymentTransactionProvider
    ) {
    }

    /**
     * @param array<string,string> $cardData
     */
    public function getHash(array $cardData): string
    {
        return sha1(implode('|', array_filter(array_values($cardData))));
    }

    public function createIfNotExist(string $cardHash, PaymentTransaction $paymentTransaction): ?PaymentTransaction
    {
        $savedCards = $this->findRecords(
            $paymentTransaction->getPaymentMethod(),
            $paymentTransaction->getFrontendOwner()
        );

        foreach ($savedCards as $savedCard) {
            if (hash_equals($this->getHash($savedCard->getResponse()), $cardHash)) {
                return null;
            }
        }

        return (new PaymentTransaction())
            ->setAction(TpayCardMethod::SAVED_CARD)
            ->setPaymentMethod($paymentTransaction->getPaymentMethod())
            ->setEntityClass($paymentTransaction->getEntityClass())
            ->setEntityIdentifier((int) $paymentTransaction->getEntityIdentifier())
            ->setAmount($paymentTransaction->getAmount())
            ->setCurrency($paymentTransaction->getCurrency())
            ->setFrontendOwner($paymentTransaction->getFrontendOwner())
            ->setSuccessful(true)
            ->setActive(true);
    }

    /**
     * @return PaymentTransaction[]
     */
    public function getCards(string $paymentMethod, ?int $savedId = null): ?array
    {
        return $this->findRecords($paymentMethod, $this->customerUserProvider->getLoggedUser(), $savedId);
    }

    /**
     * @return PaymentTransaction[]
     */
    private function findRecords(string $paymentMethod, ?CustomerUser $user = null, ?int $savedId = null): array
    {
        if ($user === null || $paymentMethod === '') {
            return [];
        }

        return $this->doctrineHelper->getEntityRepository(PaymentTransaction::class)->findBy(
            [
                'active' => true,
                'successful' => true,
                'action' => TpayCardMethod::SAVED_CARD,
                'paymentMethod' => $paymentMethod,
                'frontendOwner' => $user,
                ...($savedId !== null ? ['id' => $savedId] : [])
            ],
            ['id' => Criteria::DESC]
        );
    }

    public function save(PaymentTransaction $paymentTransaction): void
    {
        $this->paymentTransactionProvider->savePaymentTransaction($paymentTransaction);
    }
}
