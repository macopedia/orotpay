<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload\Method;

use JsonException;
use Macopedia\Bundle\TpayBundle\Tpay\PayGroup;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\CreateTransactionPayload;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadSanitizerInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use RuntimeException;

use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class VisaMobilePayloadProcessor implements PayloadProcessorInterface, PayloadSanitizerInterface
{
    public function __construct(protected CreateTransactionPayload $createTransactionPayload)
    {
    }


    public function supports(PaymentMethod $method): bool
    {
        return $method === PaymentMethod::VISA_MOBILE;
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

        $phone = $data['phone'] ?? '';

        if ($phone === '') {
            throw new RuntimeException('Visa Mobile phone not set');
        }

        // overwrite billing phone for phone number from the payment form
        $payload['payer']['phone'] = $phone;
        $payload['pay']['groupId'] = PayGroup::VISA_MOBILE->value;

        return $payload;
    }

    public function sanitize(array $payload): array
    {
        return $payload;
    }
}
