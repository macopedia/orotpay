<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

interface TpayChannelProviderInterface
{
    /**
     * @return array<int, array<string,mixed>>
     */
    public function getPaymentChannels(TpayConfigInterface $config, PaymentContextInterface $context): array;
    public function isChannelApplicable(TpayConfigInterface $config, int $channelId, PaymentContextInterface $context): bool;
}
