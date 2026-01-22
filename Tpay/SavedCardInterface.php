<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface SavedCardInterface
{
    /**
     * @param array<string,string> $cardData
     */
    public function getHash(array $cardData): string;
    public function createIfNotExist(string $cardHash, PaymentTransaction $paymentTransaction): ?PaymentTransaction;
    /**
     * @return PaymentTransaction[]
     */
    public function getCards(string $paymentMethod, ?int $savedId = null);
    public function save(PaymentTransaction $paymentTransaction): void;
}
