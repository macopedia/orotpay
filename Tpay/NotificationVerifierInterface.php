<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;

interface NotificationVerifierInterface
{
    public function create(TpayConfigInterface $config): BasicPayment;
}
