<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface TransactionPayloadInterface
{
    /**
     * @return array<string, string|float|array<string, string>>
     */
    public function process(PaymentTransaction $paymentTransaction): array;
}
