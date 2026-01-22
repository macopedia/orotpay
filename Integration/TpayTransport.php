<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Integration;

use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;
use Macopedia\Bundle\TpayBundle\Form\Type\GatewaySettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class TpayTransport implements TransportInterface
{
    public function init(Transport $transportEntity): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'macopedia.tpay.settings.transport.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType(): string
    {
        return GatewaySettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN(): string
    {
        return GatewaySettings::class;
    }
}
