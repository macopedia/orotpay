<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Controller;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    #[Route('/tpay/transactions', name: 'tpay_transactions_list')]
    #[Template('@OroTpay/Transaction/index.html.twig')]
    #[Acl(id: 'tpay_transactions_list', type: 'entity', class: PaymentTransaction::class, permission: 'VIEW')]
    public function indexAction(): array
    {
        return [
            'entity_class' => PaymentTransaction::class
        ];
    }
}
