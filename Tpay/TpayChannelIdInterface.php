<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

interface TpayChannelIdInterface
{
    public function getChannelId(): int;
}
