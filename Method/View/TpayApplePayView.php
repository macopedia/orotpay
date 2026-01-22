<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\TpayApplePayMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

class TpayApplePayView extends AbstractTpayView
{
    public function __construct(
        protected FormFactoryInterface $formFactory,
        protected TpayLegalInterface $tpayLegal,
        protected RouterInterface $router,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    ) {
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return [
                'cssClass' => 'hidden tpay-apple-pay',
                'componentOptions' => [
                    'sessionEndpoint' => $this->router->generate('tpay_apple_pay_create_session', ['id' => $this->requestStack->getCurrentRequest()?->get('id') ?? null]),
                    'methodData' => [
                        [
                            'data' => [
                                'merchantIdentifier' => $this->config->getAppleMerchantId(),
                            ],
                            'countryCode' => $context->getShippingOrigin()->getCountry()?->getCode() ?? 'PL',
                        ]
                    ],
                    'merchantId' => $this->config->getAppleMerchantId(),
                    'paymentDetails' => [
                        'total' => [
                            'label' => $this->translator->trans('macopedia.tpay.apple_pay_label'),
                            'amount' => [
                                'value' => $context->getTotal(),
                                'currency' => $context->getCurrency() ?? 'PLN',
                            ]
                        ]
                    ],
                ],
            ] + $this->getLegal();
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getApplePayLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getApplePayLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getApplePayLabel();
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayApplePayMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_apple_pay_widget';
    }
}
