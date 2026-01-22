<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\Cache;

class TpayGateway extends TpayApi implements TpayGatewayInterface
{
    public function __construct(
        Cache $cache,
        string $clientId,
        string $clientSecret,
        bool $productionMode = false,
        ?string $apiUrlOverride = null,
        ?string $clientName = null
    ) {
        parent::__construct($cache, $clientId, $clientSecret, $productionMode, $apiUrlOverride, $clientName);
    }
}
