<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Response;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $response
     */
    public function createFrom(array $response, ?callable $isSuccessfulCallback = null): ResponseInterface
    {
        $transactionId = $response['transactionId'] ?? '';
        $transactionStatus = $response['status'] ?? '';

        try {
            if ($transactionId === '') {
                throw new RuntimeException('Transaction ID cannot be empty.');
            }

            if ($transactionStatus === '') {
                throw new RuntimeException('Transaction status cannot be empty.');
            }

            $isSuccessful = ($response['result'] ?? 'failed') === 'success';

            if ($isSuccessfulCallback !== null) {
                $isSuccessful = $isSuccessfulCallback($response);
            }

            return new Response(
                isSuccessful: $isSuccessful,
                transactionId: $transactionId,
                transactionStatus: $transactionStatus,
                paymentUrl: $response['transactionPaymentUrl'] ?? '',
                data: $response
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e, 'response' => $response]);

            return $this->createEmpty($response);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createEmpty(array $data = []): ResponseInterface
    {
        return new Response(isSuccessful: false, transactionId: '', transactionStatus: '', paymentUrl: '', data: $data);
    }
}
