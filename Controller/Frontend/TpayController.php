<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Controller\Frontend;

use Doctrine\Persistence\ManagerRegistry;
use Macopedia\Bundle\TpayBundle\Form\Type\RetryForm;
use Macopedia\Bundle\TpayBundle\Method\TpayApplePayMethod;
use Macopedia\Bundle\TpayBundle\Provider\RetryMethodProvider;
use Oro\Bundle\ActionBundle\Model\ActionExecutor;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Provider\CheckoutPaymentContextProvider;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Manager\PaymentStatusManager;
use Oro\Bundle\PaymentBundle\Method\Provider\ApplicablePaymentMethodsProvider;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Override;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Webmozart\Assert\Assert;

use function array_merge;

class TpayController extends AbstractController
{
    #[Route(
        path: '/status/{accessIdentifier}',
        name: 'tpay_payment_status',
        requirements: ['accessIdentifier' => '[a-zA-Z0-9\-]+'],
        methods: ['POST']
    )]
    #[ParamConverter('paymentTransaction', options: ['mapping' => ['accessIdentifier' => 'accessIdentifier']])]
    public function statusAction(PaymentTransaction $paymentTransaction): JsonResponse
    {
        $entity = $this->container->get('doctrine')->getRepository($paymentTransaction->getEntityClass())->findOneBy(['id' => $paymentTransaction->getEntityIdentifier()]);

        if ($entity === null) {
            return new JsonResponse(['status' => 'declined'], 200);
        }

        return new JsonResponse(['status' => $this->container->get('oro_payment.manager.payment_status')->getPaymentStatus($entity)?->getPaymentStatus() ?? 'error']);
    }

    #[Route(
        path: '/retry/{id}',
        name: 'tpay_payment_retry',
        requirements: ['id' => '\d+'],
    )]
    #[Layout()]
    #[AclAncestor('oro_order_frontend_view')]
    public function retryAction(Order $order, Request $request): array|RedirectResponse
    {
        $paymentMethod = $this->container->get('macopedia_tpay.retry.method_provider')->getPaymentMethod($order);

        if (null === $paymentMethod) {
            return $this->redirect(
                $this->container->get('router')
                    ->generate('oro_order_frontend_view', ['id' => $order->getId()])
            );
        }

        $form = $this->createForm(RetryForm::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $router = $this->container->get('router');
                $result = $this->container->get('oro_action.action_executor')->executeAction(
                    'payment_purchase',
                    [
                        'attribute' => new PropertyPath('responseData'),
                        'object' => $order,
                        'amount' => $order->getTotal(),
                        'currency' => $order->getCurrency(),
                        'paymentMethod' => $paymentMethod,
                        'transactionOptions' => [
                            'successUrl' => $router->generate(
                                'tpay_payment_retry_status',
                                ['status' => 'success', 'id' => $order->getId()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            ),
                            'errorUrl' => $router->generate(
                                'tpay_payment_retry_status',
                                ['status' => 'error', 'id' => $order->getId()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            ),
                        ]
                    ]
                );

                if ($result['responseData']['successful'] ?? false) {
                    return $this->redirect($result['responseData']['paymentUrl']);
                }

                return $this->redirect($result['responseData']['errorUrl']);
            }
        }

        $router = $this->container->get('router')->generate('oro_order_frontend_print', ['id' => $order->getId()]);

        return [
            'data' => [
                'order' => $order,
                'form' => $form->createView(),
                'grid_name' => 'order-line-items-grid-frontend',
                'print_route' => ['data' => $router],
                'totals' => (object)$this->container->get(TotalProcessorProvider::class)
                    ->getTotalWithSubtotalsAsArray($order),
            ]
        ];
    }

    #[Route(
        path: '/retry/{id}/{status}',
        name: 'tpay_payment_retry_status',
        requirements: ['status' => 'success|error', 'id' => '\d+'],
    )]
    #[Layout()]
    #[AclAncestor('oro_order_frontend_view')]
    public function retryPaymentStateAction(int $id, string $status): array
    {
        return [
            'data' => [
                'status' => match ($status) {
                    'success' => 'macopedia.tpay.retry_payment.return.success',
                    'error' => 'macopedia.tpay.retry_payment.return.error'
                },
                'order_id' => $id,
            ]
        ];
    }

    #[Route(
        path: '/apple-pay-session/{id}',
        name: 'tpay_apple_pay_create_session',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[Acl(
        id: 'oro_checkout_frontend_checkout',
        type: 'entity',
        class: Checkout::class,
        permission: 'EDIT',
        groupName: 'commerce'
    )]
    public function createApplePaySessionAction(Checkout $checkout, Request $request): JsonResponse
    {
        try {
            $domainName = $request->get('domainName');
            Assert::notEmpty($domainName);
            $displayName = $request->get('displayName');
            Assert::notEmpty($displayName);
            $validationUrl = $request->get('validationUrl');
            Assert::notEmpty($validationUrl);

            $fields = [
                'domainName' => $domainName,
                'displayName' => $displayName,
                'validationUrl' => $validationUrl,
            ];

            $gateway = $this->getConfiguredGateway($checkout);

            if ($gateway === null) {
                throw new RuntimeException('Tpay gateway not found');
            }

            $response = $gateway?->transactions()?->initApplePay($fields);

            if (($response['result'] ?? '') === 'success') {
                return new JsonResponse(['session' => $response['session']]);
            }
        } catch (Throwable $e) {
            $this->container->get(LoggerInterface::class)->error($e->getMessage(), ['exception' => $e]);
            return new JsonResponse([], 400);
        }

        return new JsonResponse([], 400);
    }

    /**
     * @return TransactionsApi|null
     */
    private function getConfiguredGateway(Checkout $checkout): ?object
    {
        $context = $this->container->get('oro_checkout.provider.payment_context')->getContext($checkout);

        if (!$context) {
            return null;
        }

        foreach ($this->container->get('oro_payment.method.provider.applicable_methods_provider')->getApplicablePaymentMethods($context) as $paymentMethod) {
            if ($paymentMethod instanceof TpayApplePayMethod) {
                return $paymentMethod->getGateway();
            }
        }

        return null;
    }

    #[Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TotalProcessorProvider::class,
                LoggerInterface::class,
                'oro_payment.manager.payment_status' => PaymentStatusManager::class,
                'doctrine' => ManagerRegistry::class,
                'oro_action.action_executor' => ActionExecutor::class,
                'macopedia_tpay.retry.method_provider' => RetryMethodProvider::class,
                'oro_payment.method.provider.applicable_methods_provider' => ApplicablePaymentMethodsProvider::class,
                'oro_checkout.provider.payment_context' => CheckoutPaymentContextProvider::class
            ]
        );
    }
}
