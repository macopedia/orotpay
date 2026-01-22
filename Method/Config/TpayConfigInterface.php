<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;

interface TpayConfigInterface extends PaymentConfigInterface
{
    public function getClientId(): string;

    public function getClientSecret(): string;

    public function isProductionMode(): bool;

    public function getMerchantId(): string;

    public function getNotificationSecurityCode(): string;

    public function getMerchantRsaKey(): string;

    public function isHiddenInCheckout(): bool;

    public function getLocale(): string;

    public function getLang(): string;

    public function getBlikLabel(): string;

    public function getPayByLinkLabel(): string;

    public function getCardsLabel(): string;

    public function getPragmaPayLabel(): string;

    public function getVisaMobileLabel(): string;

    public function getApplePayLabel(): string;

    public function getGooglePayLabel(): string;
}
