<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload;

use Macopedia\Bundle\TpayBundle\Tpay\Payload\Method\PayloadProcessorInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;

interface PayloadFactoryInterface
{
    public function createFrom(PaymentMethod $method): PayloadProcessorInterface&PayloadSanitizerInterface;
}
