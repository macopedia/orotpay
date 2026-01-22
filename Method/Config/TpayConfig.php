<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

class TpayConfig extends AbstractParameterBagPaymentConfig implements TpayConfigInterface
{
    public const string FIELD_CLIENT_ID = 'client_id';
    public const string FIELD_CLIENT_SECRET = 'client_secret';
    public const string FIELD_MERCHANT_ID = 'merchant_id';
    public const string FIELD_APPLE_MERCHANT_ID = 'apple_merchant_id';
    public const string FIELD_GOOGLE_MERCHANT_ID = 'google_merchant_id';
    public const string FIELD_NOTIFICATION_SECURITY_CODE = 'notification_security_code';
    public const string FIELD_PRODUCTION_MODE = 'production_mode';
    public const string FIELD_MERCHANT_RSA_KEY = 'merchant_rsa_key';
    public const string FIELD_LANG = 'lang';
    public const string FIELD_LOCALE = 'locale';
    public const string FIELD_HIDDEN_IN_CHECKOUT = 'hidden_in_checkout';
    public const string FIELD_LABELS_BLIK = 'labels_blik';
    public const string FIELD_LABELS_CARDS = 'labels_cards';
    public const string FIELD_LABELS_PAY_BY_LINK = 'labels_pbl';
    public const string FIELD_LABELS_PRAGMA_PAY = 'labels_pragma_pay';
    public const string FIELD_LABELS_VISA_MOBILE = 'labels_visa_mobile';
    public const string FIELD_LABELS_APPLE_PAY = 'labels_apple_pay';
    public const string FIELD_LABELS_GOOGLE_PAY = 'labels_google_pay';

    public function getMerchantRsaKey(): string
    {
        return (string) $this->get(self::FIELD_MERCHANT_RSA_KEY);
    }

    public function getClientId(): string
    {
        return (string) $this->get(self::FIELD_CLIENT_ID);
    }

    public function getClientSecret(): string
    {
        return (string) $this->get(self::FIELD_CLIENT_SECRET);
    }

    public function isProductionMode(): bool
    {
        return (bool) $this->get(self::FIELD_PRODUCTION_MODE);
    }

    public function getMerchantId(): string
    {
        return (string) $this->get(self::FIELD_MERCHANT_ID);
    }

    public function getNotificationSecurityCode(): string
    {
        return (string) $this->get(self::FIELD_NOTIFICATION_SECURITY_CODE);
    }

    public function getAppleMerchantId(): string
    {
        return (string) $this->get(self::FIELD_APPLE_MERCHANT_ID);
    }

    public function getGoogleMerchantId(): string
    {
        return (string) $this->get(self::FIELD_GOOGLE_MERCHANT_ID);
    }

    public function isHiddenInCheckout(): bool
    {
        return (bool) $this->get(self::FIELD_HIDDEN_IN_CHECKOUT);
    }

    public function getLocale(): string
    {
        return (string) $this->get(self::FIELD_LOCALE);
    }

    public function getLang(): string
    {
        return (string) $this->get(self::FIELD_LANG);
    }

    public function getBlikLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_BLIK);
    }

    public function getPayByLinkLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_PAY_BY_LINK);
    }

    public function getCardsLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_CARDS);
    }

    public function getPragmaPayLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_PRAGMA_PAY);
    }

    public function getVisaMobileLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_VISA_MOBILE);
    }

    public function getApplePayLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_APPLE_PAY);
    }

    public function getGooglePayLabel(): string
    {
        return (string) $this->get(self::FIELD_LABELS_GOOGLE_PAY);
    }
}
