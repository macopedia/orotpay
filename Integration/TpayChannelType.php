<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class TpayChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    public const string TYPE = 'tpay';

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'macopedia.tpay.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'bundles/orotpay/img/tpay.png';
    }
}
