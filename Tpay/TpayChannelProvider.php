<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Macopedia\Bundle\TpayBundle\Method\Config\TpayConfigInterface;
use Oro\Bundle\CacheBundle\Generator\UniversalCacheKeyGenerator;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\ApplicablePaymentMethodsProvider;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

use function sha1;
use function str_starts_with;

class TpayChannelProvider implements TpayChannelProviderInterface
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly GatewayFactoryInterface $gatewayFactory,
        protected readonly CacheInterface $cache,
        protected readonly UniversalCacheKeyGenerator $cacheKeyGenerator,
        protected readonly ApplicablePaymentMethodsProvider $applicablePaymentMethodsProvider,
        protected int $listExpiresAfter = 300
    ) {
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function getPaymentChannels(TpayConfigInterface $config, PaymentContextInterface $context): array
    {
        return $this->cache->get(
            $this->getCacheKey('tpay_bank_list', $config),
            function (CacheItemInterface $cacheItem) use ($config, $context) {
                $cacheItem->expiresAfter($this->listExpiresAfter);

                $availableTpayMethods = array_filter(
                    $this->applicablePaymentMethodsProvider->getApplicablePaymentMethods($context),
                    static function ($paymentMethod) {
                        return str_starts_with($paymentMethod->getIdentifier(), 'tpay');
                    }
                );

                try {
                    $channels = $this->getChannels($config);
                    $response = $this->getGateway($config)->getBankGroups(true);
                } catch (Throwable $e) {
                    $this->logger->error($e->getMessage(), ['e' => $e]);
                    $response = [];
                }

                if (($response['result'] ?? '') !== 'success') {
                    return [];
                }

                $filtered = array_filter(
                    $response['groups'] ?? [],
                    function (array $item) use ($context, $channels) {
                        $channel = $channels[$item['mainChannel']] ?? null;

                        if ($channel === null || !$this->validateConstraints(
                            $channel['constraints'] ?? [],
                            $context->getTotal()
                        )) {
                            return false;
                        }

                        return $channel['available'] ?? false;
                    }
                );

                $indexedByMainChannel = [];
                foreach ($filtered as $channel) {
                    $indexedByMainChannel[$channel['mainChannel']] = $channel;
                }

                foreach ($availableTpayMethods as $tpayMethod) {
                    if (($tpayMethod instanceof TpayChannelIdInterface) && isset($indexedByMainChannel[$tpayMethod->getChannelId()])) {
                        unset($indexedByMainChannel[$tpayMethod->getChannelId()]);
                    }
                }

                return $indexedByMainChannel;
            }
        );
    }

    public function isChannelApplicable(
        TpayConfigInterface $config,
        int $channelId,
        PaymentContextInterface $context
    ): bool {
        $constraints = $this->getChannels($config)[$channelId]['constraints'] ?? [];
        return $this->validateConstraints($constraints, $context->getTotal());
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    protected function getChannels(TpayConfigInterface $config): array
    {
        return $this->cache->get(
            $this->getCacheKey('tpay_channel_list', $config),
            function (CacheItemInterface $cacheItem) use ($config) {
                $cacheItem->expiresAfter($this->listExpiresAfter);
                try {
                    $response = $this->getGateway($config)->getChannels();
                } catch (Throwable $e) {
                    $this->logger->error($e->getMessage(), ['e' => $e]);
                    $response = [];
                }

                if (($response['result'] ?? '') !== 'success') {
                    return [];
                }

                $data = [];
                foreach ($response['channels'] ?? [] as $channel) {
                    $data[$channel['id']] = $channel;
                }

                return $data;
            }
        );
    }

    /**
     * @return TransactionsApi
     */
    protected function getGateway(TpayConfigInterface $config): object
    {
        return $this->gatewayFactory->create($config)->transactions();
    }

    /**
     * @param array<int,array<string,mixed>> $constraints
     */
    protected function validateConstraints(array $constraints, float $amount): bool
    {
        foreach ($constraints as $constraint) {
            if ($constraint['field'] !== 'amount') {
                continue;
            }

            $validConstraint = match ($constraint['type']) {
                'min' => $amount >= (float)$constraint['value'],
                'max' => $amount <= (float)$constraint['value'],
                default => true,
            };

            if (!$validConstraint) {
                return false;
            }
        }

        return true;
    }

    private function getCacheKey(string $key, TpayConfigInterface $config): string
    {
        return $this->cacheKeyGenerator->generate([$key, sha1($config->getMerchantId())]);
    }
}
