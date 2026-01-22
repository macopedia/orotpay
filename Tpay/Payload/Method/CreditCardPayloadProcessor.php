<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload\Method;

use JsonException;
use Macopedia\Bundle\TpayBundle\Tpay\PayGroup;
use Macopedia\Bundle\TpayBundle\Tpay\Payload\PayloadSanitizerInterface;
use Macopedia\Bundle\TpayBundle\Tpay\PaymentMethod;
use Macopedia\Bundle\TpayBundle\Tpay\SavedCardInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use RuntimeException;

use function json_decode;

readonly class CreditCardPayloadProcessor implements PayloadProcessorInterface, PayloadSanitizerInterface
{
    public function __construct(protected SavedCardInterface $savedCard)
    {
    }

    public function supports(PaymentMethod $method): bool
    {
        return $method === PaymentMethod::CARD;
    }

    public function process(PaymentTransaction $paymentTransaction): array
    {
        try {
            $cardData = json_decode(
                $paymentTransaction->getTransactionOptions()['additionalData'] ?? '',
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $cardData = [];
        }

        $payload = [
            'save' => (bool)($cardData['saveForLater'] ?? false),
        ];

        $paymentTransaction->setTransactionOptions(array_merge(
            $paymentTransaction->getTransactionOptions(),
            ['additionalData' => null]
        ));

        $selectedAuthorizedCard = (int)($cardData['auth_card'] ?? 0);

        if ($selectedAuthorizedCard > 0) {
            $userCards = $this->savedCard->getCards($paymentTransaction->getPaymentMethod(), $selectedAuthorizedCard);

            if ($userCards === []) {
                throw new RuntimeException('Saved card is missing');
            }

            $card = reset($userCards);
            $token = $card->getResponse()['token'] ?? '';

            if ($token === '') {
                throw new RuntimeException('Saved card token is missing');
            }

            $payload['token'] = $token;
        } else {
            $payload['card'] = $cardData['card'] ?? '';
        }

        return [
            'groupId' => PayGroup::CARD->value,
            'cardPaymentData' => $payload,
        ];
    }

    public function sanitize(array $payload): array
    {
        if (isset($payload['cardPaymentData']['card'])) {
            return array_merge($payload, [
                'cardPaymentData' => [
                    'card' => '***',
                ],
            ]);
        }

        return array_merge($payload, [
            'cardPaymentData' => [
                'token' => '***',
            ],
        ]);
    }
}
