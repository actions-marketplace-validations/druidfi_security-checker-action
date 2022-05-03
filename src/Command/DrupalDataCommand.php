<?php

namespace App\Command;

use App\Service\DrupalService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrupalDataCommand extends Command
{
    protected static $defaultName = 'drupal:data';

    private DrupalService $drupalService;

    public function __construct()
    {
        $this->drupalService = new DrupalService();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        print_r($this->drupalService->getCoreReleases());

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}