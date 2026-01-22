<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Config\Factory;

use Doctrine\Common\Collections\Collection;
use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfig;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;

use function substr;

readonly class TpayConfigFactory implements TpayConfigFactoryInterface
{
    public function __construct(
        protected LocalizationHelper $localizationHelper,
        protected IntegrationIdentifierGeneratorInterface $identifierGenerator
    ) {
    }

    /**
     * @return array<string, string>
     */
    protected function normalize(GatewaySettings $settings): array
    {
        $params = [];
        $channel = $settings->getChannel();

        $params[TpayConfig::FIELD_LABEL] = $this->getLocalizedValue($settings->getLabels());
        $params[TpayConfig::FIELD_SHORT_LABEL] = $this->getLocalizedValue($settings->getShortLabels());
        $params[TpayConfig::FIELD_ADMIN_LABEL] = $channel?->getName();
        $params[TpayConfig::FIELD_PAYMENT_METHOD_IDENTIFIER] = $this->getPaymentMethodIdentifier($channel);

        $params[TpayConfig::FIELD_PRODUCTION_MODE] = $settings->isProductionMode();
        $params[TpayConfig::FIELD_CLIENT_ID] = $settings->getClientId();
        $params[TpayConfig::FIELD_CLIENT_SECRET] = $settings->getClientSecret();
        $params[TpayConfig::FIELD_MERCHANT_ID] = $settings->getMerchantId();
        $params[TpayConfig::FIELD_GOOGLE_MERCHANT_ID] = $settings->getGoogleMerchantId();
        $params[TpayConfig::FIELD_APPLE_MERCHANT_ID] = $settings->getAppleMerchantId();
        $params[TpayConfig::FIELD_NOTIFICATION_SECURITY_CODE] = $settings->getNotificationSecurityCode();
        $params[TpayConfig::FIELD_MERCHANT_RSA_KEY] = $settings->getMerchantRsaKey();
        $params[TpayConfig::FIELD_LANG] = substr($this->localizationHelper->getCurrentLocalization()?->getLanguageCode() ?? 'pl', 0, 2);
        $params[TpayConfig::FIELD_LOCALE] = $this->localizationHelper->getCurrentLocalization()?->getLanguageCode() ?? 'pl_PL';
        $params[TpayConfig::FIELD_HIDDEN_IN_CHECKOUT] = $settings->isRedirectHiddenInCheckout();

        $params[TpayConfig::FIELD_LABELS_BLIK] = $this->getLocalizedValue($settings->getBlikLabels());
        $params[TpayConfig::FIELD_LABELS_PAY_BY_LINK] = $this->getLocalizedValue($settings->getPayByLinkLabels());
        $params[TpayConfig::FIELD_LABELS_CARDS] = $this->getLocalizedValue($settings->getCardsLabels());
        $params[TpayConfig::FIELD_LABELS_APPLE_PAY] = $this->getLocalizedValue($settings->getApplePayLabels());
        $params[TpayConfig::FIELD_LABELS_GOOGLE_PAY] = $this->getLocalizedValue($settings->getGooglePayLabels());
        $params[TpayConfig::FIELD_LABELS_VISA_MOBILE] = $this->getLocalizedValue($settings->getVisaMobileLabels());
        $params[TpayConfig::FIELD_LABELS_PRAGMA_PAY] = $this->getLocalizedValue($settings->getPragmaPayLabels());

        return $params;
    }

    protected function getLocalizedValue(Collection $values): string
    {
        return (string)$this->localizationHelper->getLocalizedValue($values);
    }

    protected function getPaymentMethodIdentifier(Channel $channel): string
    {
        return $this->identifierGenerator->generateIdentifier($channel);
    }

    public function create(GatewaySettings $settings): TpayConfigInterface
    {
        return new TpayConfig($this->normalize($settings));
    }
}
