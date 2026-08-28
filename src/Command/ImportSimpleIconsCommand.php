<?php

namespace App\Command;

use App\Repository\StackRepository;
use App\Service\Icon\SimpleIconImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-simple-icons',
    description: 'Copy Simple Icons SVGs from the Composer package into public/icons/stack/',
)]
final class ImportSimpleIconsCommand extends Command
{
    public function __construct(
        private readonly StackRepository $stackRepository,
        private readonly SimpleIconImporter $iconImporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('slugs', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Icon slugs to import (e.g. react symfony php)')
            ->addOption('from-database', null, InputOption::VALUE_NONE, 'Import icons referenced by Stack.icon in the database')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files in public/icons/stack/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        /** @var list<string> $requested */
        $requested = array_values(array_unique(array_map(
            static fn (string $s): string => strtolower(trim($s)),
            $input->getArgument('slugs') ?? [],
        )));

        if ($input->getOption('from-database')) {
            $requested = array_values(array_unique([...$requested, ...$this->stackRepository->findDistinctIconSlugs()]));
        }

        if ($requested === []) {
            $io->warning('No slugs given. Example: php bin/console app:import-simple-icons react symfony php postgresql docker tailwindcss');
            $io->note('Or use --from-database to import icons referenced by Stack records.');

            return Command::INVALID;
        }

        $report = $this->iconImporter->importMany($requested, $force);

        if ($report->added() !== []) {
            $io->success(sprintf('Imported %d icon(s): %s', \count($report->added()), implode(', ', $report->added())));
        }
        if ($report->skipped() !== []) {
            $io->writeln('<comment>Skipped:</comment>');
            foreach ($report->skipped() as $line) {
                $io->writeln('  • '.$line);
            }
        }
        if ($report->errors() !== []) {
            $io->writeln('<error>Errors:</error>');
            foreach ($report->errors() as $line) {
                $io->writeln('  • '.$line);
            }
        }

        if ($report->added() === [] && $report->hasErrors()) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
