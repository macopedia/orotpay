<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Provider;

use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Factory\OrderPaymentContextFactory;
use Oro\Bundle\PaymentBundle\Manager\PaymentStatusManager;
use Oro\Bundle\PaymentBundle\Method\Provider\ApplicablePaymentMethodsProvider;
use Oro\Bundle\PaymentBundle\PaymentStatus\PaymentStatuses;

use function in_array;
use function str_ends_with;
use function str_starts_with;

class RetryMethodProvider
{
    protected ?string $retryMethod = null;

    public function __construct(
        protected ApplicablePaymentMethodsProvider $applicablePaymentMethodsProvider,
        protected OrderPaymentContextFactory $orderPaymentContextFactory,
        protected PaymentStatusManager $paymentStatusManager
    ) {
    }

    public function getPaymentMethod(Order $order): ?string
    {
        if ($this->retryMethod !== null) {
            return $this->retryMethod;
        }

        $paymentStatus = $this->paymentStatusManager->getPaymentStatus($order);

        if (!in_array($paymentStatus->getPaymentStatus(), [PaymentStatuses::DECLINED, PaymentStatuses::CANCELED], true)) {
            return null;
        }

        $methods = $this->applicablePaymentMethodsProvider
            ->getApplicablePaymentMethods(
                $this->orderPaymentContextFactory->create($order)
            );

        return $this->retryMethod = array_find_key($methods, fn ($method, $id) => str_starts_with($id, 'tpay') && str_ends_with($id, 'redirect'));
    }
}
