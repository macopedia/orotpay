<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Response;

interface ResponseInterface
{
    public const string TRANSACTION_STATUS = 'transactionStatus';
    public const string PAYMENT_URL = 'paymentUrl';

    public function isSuccessful(): bool;
    public function getTransactionId(): string;
    public function getTransactionStatus(): string;
    public function shouldRedirectFor3ds(): bool;
    public function isCorrect(): bool;
    public function getPaymentUrl(): string;
    /**
     * @return array<string, mixed>
     */
    public function getData(): array;
}
