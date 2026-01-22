<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View\Provider;

use Macopedia\Bundle\TpayBundle\Method\Config\Provider\TpayConfigProviderInterface;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\AbstractPaymentMethodViewProvider;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class TpayViewProvider extends AbstractPaymentMethodViewProvider
{
    public function __construct(
        private readonly TpayConfigProviderInterface $configProvider,
        #[TaggedIterator(tag: 'oro_tpay.payment.method.view')]
        private readonly iterable $paymentViewMethods
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildViews(): void
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->registerViewMethod($config);
        }
    }

    protected function registerViewMethod(TpayConfigInterface $config): void
    {
        foreach ($this->paymentViewMethods as $paymentViewMethod) {
            $newPaymentViewMethod = clone $paymentViewMethod;
            $newPaymentViewMethod->setConfig($config);

            $this->addView(
                $newPaymentViewMethod->getPaymentMethodIdentifier(),
                $newPaymentViewMethod
            );
        }
    }
}
