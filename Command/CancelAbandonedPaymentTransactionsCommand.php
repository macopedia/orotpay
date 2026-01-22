<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Command;

use Macopedia\Bundle\TpayBundle\Handler\CancelHandlerInterface;
use Oro\Bundle\CronBundle\Command\CronCommandActivationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('tpay:cancel-abandoned-transactions')]
class CancelAbandonedPaymentTransactionsCommand extends Command
implements CronCommandScheduleDefinitionInterface, CronCommandActivationInterface
{
    public function __construct(protected CancelHandlerInterface $handler, protected string $defaultDefinition)
    {
        parent::__construct();
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->handler->hasAnyTransactionsToCancel();
    }

    #[Override]
    public function getDefaultDefinition(): string
    {
        return $this->defaultDefinition;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting cancel abandoned transactions...');
        $this->handler->process();
        $output->writeln('Canceling abandoned transactions finished');

        return self::SUCCESS;
    }
}
