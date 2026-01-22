<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;
use Tpay\OpenApi\Utilities\Cache;
use Tpay\OpenApi\Utilities\CacheCertificateProvider;
use Tpay\OpenApi\Webhook\JWSVerifiedPaymentNotification;

class NotificationVerifierFactory implements NotificationVerifierInterface
{
    public function __construct(protected CacheInterface $cache)
    {
    }

    public function create(TpayConfigInterface $config): BasicPayment
    {
        return (new JWSVerifiedPaymentNotification(
            new CacheCertificateProvider(new Cache(null, new Psr16Cache($this->cache))),
            $config->getNotificationSecurityCode(),
            $config->isProductionMode()
        ))->getNotification();
    }
}
