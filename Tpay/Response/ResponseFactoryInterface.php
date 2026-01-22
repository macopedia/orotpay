<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay\Response;

interface ResponseFactoryInterface
{
    /**
     * @param array<string, mixed> $response
     */
    public function createFrom(array $response): ResponseInterface;
    public function createEmpty(): ResponseInterface;
}
