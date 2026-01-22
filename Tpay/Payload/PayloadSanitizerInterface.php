<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Payload;

interface PayloadSanitizerInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sanitize(array $payload): array;
}
