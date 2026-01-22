<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\Provider;

use Macopedia\Bundle\TpayBundle\Method\Config\Provider\TpayConfigProviderInterface;
use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class TpayMethodProvider extends AbstractPaymentMethodProvider
{
    public function __construct(
        private readonly TpayConfigProviderInterface $configProvider,
        #[TaggedIterator(tag: 'oro_tpay.payment.method')]
        private readonly iterable $paymentMethods
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function collectMethods(): void
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->registerMethod($config);
        }
    }

    protected function registerMethod(TpayConfigInterface $config): void
    {
        foreach ($this->paymentMethods as $method) {
            $newMethod = clone $method;

            $newMethod->setConfig($config);

            $this->addMethod(
                $newMethod->getIdentifier(),
                $newMethod
            );
        }
    }
}
