<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload\Method;

use JsonException;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\CreateTransactionPayload;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadSanitizerInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use RuntimeException;

use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class ApplePayPayloadProcessor implements PayloadProcessorInterface, PayloadSanitizerInterface
{
    private const int APPLE_PAY_CHANNEL_ID = 75;

    public function __construct(protected CreateTransactionPayload $createTransactionPayload)
    {
    }

    public function supports(PaymentMethod $method): bool
    {
        return $method === PaymentMethod::APPLE_PAY;
    }

    /**
     * @return array<string,mixed>
     */
    public function process(PaymentTransaction $paymentTransaction): array
    {
        $payload = $this->createTransactionPayload->process($paymentTransaction);

        try {
            $data = json_decode(
                $paymentTransaction->getTransactionOptions()['additionalData'] ?? '',
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $data = [];
        }

        $paymentTransaction->setTransactionOptions(array_merge($paymentTransaction->getTransactionOptions(), ['additionalData' => null]));

        $token = $data['token'] ?? '';

        if ($token === '') {
            throw new RuntimeException('Apple Pay token not set');
        }

        return $payload + [
                'pay' => [
                    'channelId' => self::APPLE_PAY_CHANNEL_ID,
                    'applePayPaymentData' => $token,
                ],
            ];
    }


    public function sanitize(array $payload): array
    {
        return array_merge($payload, [
            'pay' => [
                'applePayPaymentData' => '***',
            ]
        ]);
    }
}
