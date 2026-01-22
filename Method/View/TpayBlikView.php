<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Form\Type\BlikTokenType;
use Macopedia\Bundle\TpayBundle\Method\TpayBlikMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Form\FormFactoryInterface;

use function sprintf;

class TpayBlikView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal,
        protected FormFactoryInterface $formFactory
    ) {
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getBlikLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getBlikLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getBlikLabel();
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return parent::getOptions($context) + [
            'formView' => $this->formFactory->create(BlikTokenType::class)->createView(),
        ] ;
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayBlikMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_blik_widget';
    }
}
