<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload\Method;

use Macopedia\Bundle\TpayBundle\Tpay\Payload\CreateTransactionPayload;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadSanitizerInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

readonly class PragmaPayPayloadProcessor implements PayloadProcessorInterface, PayloadSanitizerInterface
{
    private const int PRAGMA_PAY_ID = 85;

    public function __construct(protected CreateTransactionPayload $createTransactionPayload)
    {
    }

    public function supports(PaymentMethod $method): bool
    {
        return $method === PaymentMethod::PRAGMA_PAY;
    }

    /**
     * @return array<string,mixed>
     */
    public function process(PaymentTransaction $paymentTransaction): array
    {
        $payload = $this->createTransactionPayload->process($paymentTransaction);

        return $payload + [
            'pay' => [
                'channelId' => self::PRAGMA_PAY_ID,
            ],
        ];
    }

    public function sanitize(array $payload): array
    {
        return $payload;
    }
}
