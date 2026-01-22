<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Event;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class PaymentSuccessfullyProcessedEvent
{
    public function __construct(protected PaymentTransaction $paymentTransaction)
    {
    }

    public function getPaymentTransaction(): PaymentTransaction
    {
        return $this->paymentTransaction;
    }

    public function setPaymentTransaction(PaymentTransaction $paymentTransaction): void
    {
        $this->paymentTransaction = $paymentTransaction;
    }
}
