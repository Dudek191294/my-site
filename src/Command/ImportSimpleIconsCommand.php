<?php

namespace App\Command;

use App\Repository\StackRepository;
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
    private const SLUG_PATTERN = '/^[a-z0-9]{1,80}$/';

    public function __construct(
        private readonly string $projectDir,
        private readonly StackRepository $stackRepository,
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

        $packagePath = \Composer\InstalledVersions::getInstallPath('simple-icons/simple-icons');
        if ($packagePath === null) {
            $io->error('Package simple-icons/simple-icons is not installed.');

            return Command::FAILURE;
        }

        $sourceDir = realpath($packagePath.'/icons');
        if ($sourceDir === false || !is_dir($sourceDir)) {
            $io->error(sprintf('Simple Icons directory not found under %s/icons', $packagePath));

            return Command::FAILURE;
        }

        $targetDir = $this->projectDir.'/public/icons/stack';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            $io->error(sprintf('Unable to create target directory: %s', $targetDir));

            return Command::FAILURE;
        }

        $targetReal = realpath($targetDir);
        if ($targetReal === false) {
            $io->error(sprintf('Unable to resolve target directory: %s', $targetDir));

            return Command::FAILURE;
        }

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

        $added = [];
        $skipped = [];
        $errors = [];

        foreach ($requested as $slug) {
            if ($slug === '' || preg_match(self::SLUG_PATTERN, $slug) !== 1) {
                $errors[] = sprintf('%s — invalid slug (expected a–z / 0–9 only)', $slug);

                continue;
            }

            $source = $sourceDir.\DIRECTORY_SEPARATOR.$slug.'.svg';
            $sourceReal = realpath($source);
            if ($sourceReal === false || !str_starts_with($sourceReal, $sourceDir.\DIRECTORY_SEPARATOR) || !is_file($sourceReal)) {
                $errors[] = sprintf('%s — not found in Simple Icons package', $slug);

                continue;
            }

            $destination = $targetReal.\DIRECTORY_SEPARATOR.$slug.'.svg';
            if (is_file($destination) && !$force) {
                $skipped[] = sprintf('%s — already exists (use --force to overwrite)', $slug);

                continue;
            }

            $svg = file_get_contents($sourceReal);
            if ($svg === false || !str_contains($svg, '<svg')) {
                $errors[] = sprintf('%s — source file is not a readable SVG', $slug);

                continue;
            }

            if (file_put_contents($destination, $svg) === false) {
                $errors[] = sprintf('%s — failed to write %s', $slug, $destination);

                continue;
            }

            $added[] = $slug;
        }

        if ($added !== []) {
            $io->success(sprintf('Imported %d icon(s): %s', \count($added), implode(', ', $added)));
        }
        if ($skipped !== []) {
            $io->writeln('<comment>Skipped:</comment>');
            foreach ($skipped as $line) {
                $io->writeln('  • '.$line);
            }
        }
        if ($errors !== []) {
            $io->writeln('<error>Errors:</error>');
            foreach ($errors as $line) {
                $io->writeln('  • '.$line);
            }
        }

        if ($added === [] && $errors !== []) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
