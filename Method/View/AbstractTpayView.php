<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;

abstract class AbstractTpayView implements PaymentMethodViewInterface
{
    protected TpayConfigInterface $config;

    public function setConfig(TpayConfigInterface $config): static
    {
        $this->config = $config;
        return $this;
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return $this->getLegal();
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_widget';
    }

    public function getLabel(): string
    {
        return $this->config->getLabel();
    }

    public function getAdminLabel(): string
    {
        return $this->config->getAdminLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getShortLabel();
    }

    public function getPaymentMethodIdentifier(): string
    {
        return $this->config->getPaymentMethodIdentifier();
    }


    /**
     * @return array<string, string>
     */
    protected function getLegal(): array
    {
        return [
            'tpayRegulationsUrl' => $this->tpayLegal->getRegulations(),
            'tpayPolicyUrl' => $this->tpayLegal->getPolicy(),
        ];
    }
}
