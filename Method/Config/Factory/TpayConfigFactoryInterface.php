<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config\Factory;

use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;

interface TpayConfigFactoryInterface
{
    public function create(GatewaySettings $settings): TpayConfigInterface;
}
