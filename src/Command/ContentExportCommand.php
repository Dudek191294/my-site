<?php

namespace App\Command;

use App\Service\Content\PortfolioContentExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'app:content:export',
    description: 'Export CMS content (not users) to a YAML file for Git',
)]
final class ContentExportCommand extends Command
{
    public function __construct(
        private readonly PortfolioContentExporter $exporter,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Target YAML file', 'content/portfolio.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->resolvePath((string) $input->getOption('file'));

        $directory = \dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            $io->error(sprintf('Cannot create directory "%s".', $directory));

            return Command::FAILURE;
        }

        $snapshot = $this->exporter->export();
        $yaml = Yaml::dump($snapshot, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_NULL_AS_TILDE);
        $header = <<<'YAML'
# Treść CMS strony (projekty, stack, doświadczenie, ustawienia, linki).
# Nie zawiera kont użytkowników — te zostają na danym środowisku.
#
# Klucze upsert przy imporcie:
#   stack          → name
#   project        → slug
#   experience     → company + role + start_date
#   social_link    → platform
#   site_setting   → pojedynczy wiersz
#
# Import nie kasuje rekordów, których nie ma w tym pliku.
#   php bin/console app:content:export
#   php bin/console app:content:import

YAML;

        if (file_put_contents($path, $header.$yaml) === false) {
            $io->error(sprintf('Cannot write "%s".', $path));

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Exported %d stack(s), %d project(s), %d experience(s), %d social link(s)%s to %s',
            \count($snapshot['stacks']),
            \count($snapshot['projects']),
            \count($snapshot['experiences']),
            \count($snapshot['social_links']),
            $snapshot['site_setting'] === null ? ', no site settings' : ', site settings',
            $this->relativePath($path),
        ));

        return Command::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        if (str_starts_with($file, '/')) {
            return $file;
        }

        return $this->projectDir.'/'.$file;
    }

    private function relativePath(string $path): string
    {
        return str_starts_with($path, $this->projectDir.'/')
            ? substr($path, \strlen($this->projectDir) + 1)
            : $path;
    }
}
