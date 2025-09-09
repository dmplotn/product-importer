<?php

namespace App\Command;

use App\Service\ProductImport\ProductImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'import-products',
    description: 'Add a short description for your command',
)]
class ImportProductsCommand extends Command
{
    private ProductImportService $service;

    public function __construct(ProductImportService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'file',
            null,
            InputArgument::REQUIRED,
            'Path to import file',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        try {
            $importReport = $this->service->import($filePath);
            $output->writeln(sprintf('<info>Added items count: %s</info>', $importReport->getAddedItemsCount()));
            $output->writeln(sprintf('<info>Added items total: %s</info>', $importReport->getAddedItemsTotal()));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
