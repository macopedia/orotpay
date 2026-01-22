<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;

interface GatewayFactoryInterface
{
    public function create(TpayConfigInterface $config): TpayGatewayInterface;
}
