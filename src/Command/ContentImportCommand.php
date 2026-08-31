<?php

namespace App\Command;

use App\Service\Content\PortfolioContentImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'app:content:import',
    description: 'Upsert CMS content from YAML without deleting users or extra rows',
)]
final class ContentImportCommand extends Command
{
    public function __construct(
        private readonly PortfolioContentImporter $importer,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Source YAML file', 'content/portfolio.yaml')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate and apply in a rolled-back transaction');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getOption('file'));
        $dryRun = (bool) $input->getOption('dry-run');

        if (!is_file($path)) {
            $io->error(sprintf('File not found: %s', $path));

            return Command::FAILURE;
        }

        $parsed = Yaml::parseFile($path);
        if (!\is_array($parsed)) {
            $io->error('YAML root must be a mapping.');

            return Command::FAILURE;
        }

        try {
            $report = $this->importer->import($parsed, $dryRun);
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->definitionList(
            ['Site settings' => $report->siteSettingWritten ? 'written' : 'skipped (null in file)'],
            ['Stacks' => sprintf('%d created, %d updated', $report->stacksCreated, $report->stacksUpdated)],
            ['Projects' => sprintf('%d created, %d updated', $report->projectsCreated, $report->projectsUpdated)],
            ['Experience' => sprintf('%d created, %d updated', $report->experiencesCreated, $report->experiencesUpdated)],
            ['Social links' => sprintf('%d created, %d updated', $report->socialLinksCreated, $report->socialLinksUpdated)],
        );

        if ($report->warnings !== []) {
            $io->warning($report->warnings);
        }

        if ($dryRun) {
            $io->success('Dry-run OK — no changes were committed.');
        } else {
            $io->success('CMS content imported (users were not touched).');
        }

        return Command::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        if (str_starts_with($file, '/')) {
            return $file;
        }

        return $this->projectDir.'/'.$file;
    }
}
