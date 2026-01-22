<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\EventListener;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Throwable;

use function in_array;
use function json_decode;
use function json_encode;

class CreateOrderEventListener
{
    public function onCreateOrder(ExtendableActionEvent $event): void
    {
        $checkout = $event->getData()->offsetGet('checkout');

        if ($checkout instanceof Checkout) {
            try {
                $additionalData = json_decode((string)$checkout->getAdditionalData(), true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                $additionalData = [];
            }

            $data = [];
            foreach ($additionalData as $key => $value) {
                if (in_array($key, ['token', 'blik_token', 'card', 'auth_card', 'phone'], true)) {
                    continue;
                }

                $data[$key] = $value;
            }

            try {
                $checkout->setAdditionalData(json_encode($data, JSON_THROW_ON_ERROR));
            } catch (Throwable) {

            }
        }
    }
}
