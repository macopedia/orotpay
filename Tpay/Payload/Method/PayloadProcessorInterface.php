<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload\Method;

use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

interface PayloadProcessorInterface
{
    public function supports(PaymentMethod $method): bool;
    /**
     * @return array<string,mixed>
     */
    public function process(PaymentTransaction $paymentTransaction): array;
}
