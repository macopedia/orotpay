<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;
use Macopedia\Bundle\TpayBundle\Entity\Repository\GatewaySettingsRepository;
use Macopedia\Bundle\TpayBundle\Method\Config\Factory\TpayConfigFactoryInterface;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

readonly class TpayConfigProvider implements TpayConfigProviderInterface
{
    public function __construct(
        protected ManagerRegistry $doctrine,
        protected LoggerInterface $logger,
        protected TpayConfigFactoryInterface $configFactory,
        protected string $type
    ) {
    }

    /**
     * @return array<string,TpayConfigInterface>
     */
    public function getPaymentConfigs(): array
    {
        $configs = [];

        foreach ($this->getEnabledIntegrationSettings() as $setting) {
            $config = $this->configFactory->create($setting);
            $configs[$config->getPaymentMethodIdentifier()] = $config;
        }

        return $configs;
    }

    /**
     * @return GatewaySettings[]
     */
    protected function getEnabledIntegrationSettings(): array
    {
        try {
            /** @var GatewaySettingsRepository $repository */
            $repository = $this->doctrine
                ->getManagerForClass(GatewaySettings::class)
                ->getRepository(GatewaySettings::class);

            return $repository->getEnabledSettings($this->getType());
        } catch (UnexpectedValueException $e) {
            $this->logger->critical($e->getMessage());

            return [];
        }
    }

    private function getType(): string
    {
        return $this->type;
    }
}
