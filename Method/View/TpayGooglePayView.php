<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\TpayGooglePayMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Form\FormFactoryInterface;

use function sprintf;

class TpayGooglePayView extends AbstractTpayView
{
    public function __construct(
        protected FormFactoryInterface $formFactory,
        protected TpayLegalInterface $tpayLegal
    ) {
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return [
                'cssClass' => 'hidden tpay-google-pay',
                'componentOptions' => [
                    'isProduction' => $this->config->isProductionMode(),
                    'merchantId' => $this->config->getGoogleMerchantId(),
                    'gatewayMerchantId' => $this->config->getMerchantId(),
                    'transactionInfo' => [
                        'totalPriceStatus' => 'FINAL',
                        'countryCode' => $context->getShippingOrigin()->getCountry()?->getCode() ?? 'PL',
                        'currencyCode' => $context->getCurrency() ?? 'PLN',
                        'totalPrice' => $context->getTotal()
                    ]
                ],

            ] + $this->getLegal();
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getGooglePayLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getGooglePayLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getGooglePayLabel();
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayGooglePayMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_google_pay_widget';
    }
}
