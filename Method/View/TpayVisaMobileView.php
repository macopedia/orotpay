<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Macopedia\Bundle\TpayBundle\Form\Type\VisaMobileType;
use Macopedia\Bundle\TpayBundle\Method\TpayVisaMobileMethod;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Symfony\Component\Form\FormFactoryInterface;

use function sprintf;

class TpayVisaMobileView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal,
        protected FormFactoryInterface $formFactory
    ) {
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayVisaMobileMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getVisaMobileLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getVisaMobileLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getVisaMobileLabel();
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        return parent::getOptions($context) + [
            'formView' => $this->formFactory->create(VisaMobileType::class)->createView(),
        ];
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_visa_mobile_widget';
    }
}
