<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Handler;

interface CancelHandlerInterface
{
    public function hasAnyTransactionsToCancel(): bool;
    public function process(): void;
}
