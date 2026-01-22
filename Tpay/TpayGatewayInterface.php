<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Tpay\OpenApi\Api\Transactions\TransactionsApi;

interface TpayGatewayInterface
{
    /**
     * @return TransactionsApi
     */
    public function transactions();
}
