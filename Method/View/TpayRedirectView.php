<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\TpayRedirectMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;

use function sprintf;

class TpayRedirectView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal
    ) {
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayRedirectMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getShortLabel();
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_redirect_widget';
    }
}
