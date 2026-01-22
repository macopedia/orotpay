<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\TpayPayByLinkMethod;
use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayChannelProviderInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Form\FormFactoryInterface;

use function sprintf;

class TpayPayByLinkView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal,
        protected FormFactoryInterface $formFactory,
        protected GatewayFactoryInterface $gatewayFactory,
        protected TpayChannelProviderInterface $tpayChannelProvider
    ) {
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayPayByLinkMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getPayByLinkLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getPayByLinkLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getPayByLinkLabel();
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return parent::getOptions($context) + [
            'channels' => $this->getChannels($context),
        ];
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_pay_by_link_widget';
    }

    private function getChannels(PaymentContextInterface $context): array
    {
        return $this->tpayChannelProvider->getPaymentChannels($this->config, $context);
    }
}
