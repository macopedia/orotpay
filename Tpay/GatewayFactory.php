<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface;
use Tpay\OpenApi\Utilities\Cache;
use Tpay\OpenApi\Utilities\Logger;

class GatewayFactory implements GatewayFactoryInterface
{
    public function __construct(protected CacheInterface $cache, protected ?LoggerInterface $logger = null)
    {
    }

    public function create(TpayConfigInterface $config): TpayGatewayInterface
    {
        if ($this->logger) {
            Logger::setLogger($this->logger);
        } else {
            Logger::disableLogging();
        }

        return new TpayGateway(
            new Cache(null, new Psr16Cache($this->cache)),
            $config->getClientId(),
            $config->getClientSecret(),
            $config->isProductionMode(),
            clientName: 'OroCommerce'
        );
    }
}
