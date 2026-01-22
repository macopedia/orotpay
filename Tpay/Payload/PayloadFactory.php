<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload;

use Macopedia\Bundle\TpayBundle\Tpay\Payload\Method\PayloadProcessorInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class PayloadFactory implements PayloadFactoryInterface
{
    /**
     * @param iterable<PayloadProcessorInterface> $payloadProcessors
     */
    public function __construct(
        #[AutowireIterator(tag: 'oro_tpay.payment.method.payload.processor')]
        protected iterable $payloadProcessors
    ) {
    }

    public function createFrom(PaymentMethod $method): PayloadProcessorInterface&PayloadSanitizerInterface
    {
        foreach ($this->payloadProcessors as $processor) {
            if ($processor->supports($method)) {
                return $processor;
            }
        }

        throw new RuntimeException('Unsupported payment method');
    }
}
