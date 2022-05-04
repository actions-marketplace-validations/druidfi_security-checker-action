<?php

namespace App\Command;

use App\Service\UpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command
{
    protected static $defaultName = 'check';

    private UpdateService $updateService;

    protected function configure()
    {
        $this->updateService = new UpdateService();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists('composer.json')) {
            $output->writeln('<error>Unable to read composer.json</error>');
            return Command::FAILURE;
        }

        try {
            $this->updateService->checkUpdates();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
