<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config\Provider;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;

interface TpayConfigProviderInterface
{
    /**
     * @return TpayConfigInterface[]
     */
    public function getPaymentConfigs();
}
