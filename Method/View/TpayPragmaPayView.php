<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\TpayPragmaPayMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;

use function sprintf;

class TpayPragmaPayView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal
    ) {
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayPragmaPayMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getPragmaPayLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getPragmaPayLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getPragmaPayLabel();
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_pragma_pay_widget';
    }
}
