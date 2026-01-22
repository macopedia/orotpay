<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\EventListener;

use Macopedia\Bundle\TpayBundle\Event\PaymentSuccessfullyProcessedEvent;
use Macopedia\Bundle\TpayBundle\Method\AbstractTpayMethod;
use Oro\Bundle\PaymentBundle\Event\AbstractCallbackEvent;
use Oro\Bundle\PaymentBundle\Event\CallbackErrorEvent;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Tpay\OpenApi\Utilities\TpayException;

use function in_array;

class GatewayListener
{
    use LoggerAwareTrait;

    public function __construct(
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
        private PaymentMethodProviderInterface $paymentMethodProvider
    ) {
    }

    public function onError(CallbackErrorEvent $event): void
    {
        $paymentTransaction = $event->getPaymentTransaction();

        if (!$paymentTransaction) {
            return;
        }

        if (false === $this->paymentMethodProvider->hasPaymentMethod($paymentTransaction->getPaymentMethod())) {
            return;
        }

        $paymentTransaction
            ->setSuccessful(false)
            ->setActive(false);
    }

    public function onNotify(AbstractCallbackEvent $event): void
    {
        $paymentTransaction = $event->getPaymentTransaction();

        if (!$paymentTransaction || !$paymentTransaction->getReference()) {
            return;
        }

        $paymentMethodId = $paymentTransaction->getPaymentMethod();

        if (false === $this->paymentMethodProvider->hasPaymentMethod($paymentMethodId)) {
            return;
        }

        try {
            $paymentMethod = $this->paymentMethodProvider->getPaymentMethod($paymentMethodId);

            if (in_array($paymentTransaction->getAction(), [
                AbstractTpayMethod::FAILED,
                PaymentMethodInterface::CANCEL,
            ], true)) {
                $this->successResponse($event);
                return;
            }

            $responseDataFilledWithEventData = array_replace(
                $paymentTransaction->getResponse(),
                ['notification' => $event->getData()]
            );

            $paymentTransaction->setResponse($responseDataFilledWithEventData);

            $paymentMethod->execute(AbstractTpayMethod::COMPLETE, $paymentTransaction);

            if ($paymentTransaction->isSuccessful() &&
                !($paymentTransaction->getResponse()['notificationProcessed'] ?? false) &&
                in_array($paymentTransaction->getAction(), [
                    PaymentMethodInterface::PURCHASE,
                    PaymentMethodInterface::REFUND,
                ], true)
            ) {
                $paymentTransaction->setResponse(
                    array_merge(
                        $paymentTransaction->getResponse(),
                        ['notificationProcessed' => true]
                    )
                );
                $this->eventDispatcher->dispatch(
                    new PaymentSuccessfullyProcessedEvent($paymentTransaction),
                    'macopedia_tpay.gateway.successful_payment'
                );
            }

            $this->successResponse($event);
        } catch (TpayException $e) {
            $this->logger?->error($e->getMessage());

            $event->setResponse(new Response('FALSE -' . $e->getMessage()));
        } catch (Throwable $e) {
            $this->logger?->error($e->getMessage());

            $event->setResponse(new Response('FALSE'));
        }
    }

    private function successResponse(AbstractCallbackEvent $event): void
    {
        $event->setResponse(new Response('TRUE'));
        $event->markSuccessful();
    }
}
