<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function str_starts_with;
use function strip_tags;

readonly class CreateTransactionPayload implements TransactionPayloadInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected DoctrineHelper $doctrineHelper,
        protected TranslatorInterface $translator,
        protected LocalizationProviderInterface $localizationProvider,
        protected RequestStack $requestStack,
        protected ?string $taxField = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function process(PaymentTransaction $paymentTransaction): array
    {
        $order = $this->getEntity($paymentTransaction);

        if (null === $order) {
            return [];
        }

        return [
            'amount' => number_format((float)$paymentTransaction->getAmount(), 2, thousands_separator: ''),
            'description' => $this->translator->trans(
                'macopedia.tpay.transaction_description',
                ['%orderId%' => $order?->getIdentifier()],
            ),
            'hiddenDescription' => $order?->getIdentifier(),
            'lang' => substr($this->localizationProvider->getCurrentLocalization()?->getLanguageCode() ?? 'pl', 0, 2),
            'payer' => $this->generatePayerPayload($order),
            'callbacks' => $this->generateCallbacksUrls($paymentTransaction),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function generatePayerPayload(Order $order): array
    {
        $customerEmail = $order->getEmail();
        $billingAddress = $order->getBillingAddress();

        if (!$customerEmail) {
            return [];
        }

        $request = $this->requestStack->getCurrentRequest();
        $requiredFields = [
            'email' => $order->getEmail(),
            'name' => $order->getEmailHolderName(),
        ];

        $extraFields = [
            // address
            'phone' => $billingAddress?->getPhone() ?? '',
            'address' => $billingAddress?->getCustomerUserAddress()?->getStreet() ?? $billingAddress?->getCustomerAddress()?->getStreet() ?? '',
            'city' => $billingAddress?->getCustomerUserAddress()?->getCity() ?? $billingAddress?->getCustomerAddress()?->getCity() ?? '',
            'code' => $billingAddress?->getCustomerUserAddress()?->getPostalCode() ?? $billingAddress?->getCustomerAddress()?->getPostalCode() ?? '',
            'country' => $billingAddress?->getCustomerUserAddress()?->getCountry()?->getIso2Code() ?? $billingAddress?->getCustomerAddress()?->getCountry()?->getIso2Code() ?? '',
            // extra fields
            'ip' => $request?->getClientIp(),
            'userAgent' => substr(strip_tags((string) $request?->headers->get('User-Agent')), 0, 255),
            'taxId' => $this->getCustomerTaxId($order),
        ];

        $extraFields = array_filter($extraFields, static function (string $value): bool {
            return $value !== '';
        });

        return array_merge($requiredFields, $extraFields);
    }

    protected function getEntity(PaymentTransaction $paymentTransaction): ?Order
    {
        $entity = $this->doctrineHelper->getEntityReference(
            $paymentTransaction->getEntityClass(),
            $paymentTransaction->getEntityIdentifier()
        );

        if (!$entity instanceof Order) {
            return null;
        }

        return $entity;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function generateCallbacksUrls(PaymentTransaction $paymentTransaction): array
    {
        return [
            'payerUrls' => [
                'success' => $this->getUrlFromOptions($paymentTransaction, 'successUrl') ?? $this->router->generate(
                    'oro_payment_callback_return',
                    ['accessIdentifier' => $paymentTransaction->getAccessIdentifier()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'error' => $this->getUrlFromOptions($paymentTransaction, 'errorUrl') ?? $this->router->generate(
                    'oro_payment_callback_error',
                    ['accessIdentifier' => $paymentTransaction->getAccessIdentifier()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            'notification' => [
                'url' => $this->router->generate(
                    'oro_payment_callback_notify',
                    [
                        'accessIdentifier' => $paymentTransaction->getAccessIdentifier(),
                        'accessToken' => $paymentTransaction->getAccessToken(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
        ];
    }

    protected function getCustomerTaxId(Order $order): string
    {
        $taxField = trim((string)$this->taxField);

        if ($taxField === '') {
            return '';
        }

        return (string)$order->getCustomer()?->get($this->taxField);
    }

    private function getUrlFromOptions(PaymentTransaction $paymentTransaction, string $option): ?string
    {
        $url = $paymentTransaction->getTransactionOptions()[$option] ?? '';

        if (!str_starts_with($url, 'http')) {
            return null;
        }
        return $url;
    }
}
