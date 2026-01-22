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

readonly class PayByLinkPayloadProcessor implements PayloadProcessorInterface, PayloadSanitizerInterface
{
    public function __construct(protected CreateTransactionPayload $createTransactionPayload)
    {
    }

    public function supports(PaymentMethod $method): bool
    {
        return $method === PaymentMethod::PAY_BY_LINK;
    }

    /**
     * @return array<string,mixed>
     */
    public function process(PaymentTransaction $paymentTransaction): array
    {
        try {
            $payload = $this->createTransactionPayload->process($paymentTransaction);

            $data = json_decode(
                $paymentTransaction->getTransactionOptions()['additionalData'] ?? '',
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $data = [];
        }

        $channelId = (int) ($data['channelId'] ?? 0);

        if ($channelId < 1) {
            throw new RuntimeException('Channel id not set');
        }

        return $payload + [
            'pay' => [
                'channelId' => $channelId,
            ]
        ];
    }

    public function sanitize(array $payload): array
    {
        return $payload;
    }
}
