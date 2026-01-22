<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

use function array_filter;
use function str_starts_with;

class TpayPaymentGridListener
{
    public function __construct(protected ManagerRegistry $managerRegistry)
    {
    }

    public function onBuildBefore(BuildBefore $event): void
    {
        $config = $event->getConfig();
        $dataGrid = $event->getDatagrid();

        if (!$this->hasTpayPayment($dataGrid) && $config->getName() !== 'tpay-payments') {
            return;
        }

        $config->addTwigColumn(
            'title',
            'macopedia.tpay.datagrid.transaction_title.label',
            '@OroTpay/Order/grid/payment_title.html.twig',
            'payment_transaction.response'
        );

        $config->addColumn(
            'reference',
            [
                'label' => 'macopedia.tpay.datagrid.transaction_id.label',
            ],
            'payment_transaction.reference'
        );

        $config->addTwigColumn(
            'log',
            'LOG',
            '@OroTpay/Order/grid/payment_transactions.html.twig',
            'payment_transaction.response, payment_transaction.request, payment_transaction.webhookRequestLogs'
        );
    }

    private function hasTpayPayment(DatagridInterface $dataGrid): bool
    {
        $orderId = $dataGrid->getParameters()->get('order_id');

        if (!$orderId) {
            return false;
        }

        $orderPaymentMethods = array_filter(
            $this->getPaymentMethodsForOrder($orderId),
            static function (string $paymentMethod) {
                return str_starts_with($paymentMethod, 'tpay_');
            }
        );

        return $orderPaymentMethods !== [];
    }

    private function getPaymentMethodsForOrder(int $orderId): array
    {
        $repository = $this->managerRegistry->getRepository(PaymentTransaction::class);
        $methodsByOrderId = $repository->getPaymentMethods(Order::class, [$orderId]);

        return $methodsByOrderId[$orderId] ?? [];
    }
}
