<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Response;

use ArrayAccess;

readonly class Response implements ResponseInterface, ArrayAccess
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected bool $isSuccessful,
        protected string $transactionId,
        protected string $transactionStatus,
        protected string $paymentUrl,
        protected array $data
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getTransactionStatus(): string
    {
        return $this->transactionStatus;
    }

    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    public function shouldRedirectFor3ds(): bool
    {
        return !$this->isCorrect();
    }

    public function isCorrect(): bool
    {
        return ($this->data['status'] ?? '') === 'correct';
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->data[$offset] ?? false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
