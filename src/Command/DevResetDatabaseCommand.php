<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Drops and rebuilds the local development database only.
 * Hidden unless APP_ENV=dev. Never run this on production.
 */
#[AsCommand(
    name: 'app:dev:reset-database',
    description: 'DEV ONLY: drop database app, recreate it, run migrations, optionally import CMS YAML',
)]
final class DevResetDatabaseCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    public function isEnabled(): bool
    {
        return $this->kernel->getEnvironment() === 'dev';
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Required together with --no-interaction')
            ->addOption('with-content', null, InputOption::VALUE_NONE, 'Import content/portfolio.yaml after migrations')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'YAML file used with --with-content', 'content/portfolio.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->kernel->getEnvironment() !== 'dev') {
            $io->error('Refusing to run: this command is only available in the dev environment.');

            return Command::FAILURE;
        }

        $databaseUrl = (string) ($_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '');
        $safeUrl = preg_replace('#://([^:/]+):([^@]+)@#', '://$1:***@', $databaseUrl) ?? $databaseUrl;

        $io->warning([
            'This DROPS the entire database and recreates an empty schema from migrations.',
            'It must never be used on production.',
            'Target: '.$safeUrl,
        ]);

        if (!$input->getOption('force')) {
            if (!$io->confirm('Drop the local development database and rebuild it from migrations?', false)) {
                $io->info('Aborted.');

                return Command::SUCCESS;
            }
        }

        $steps = [
            ['doctrine:database:drop', '--force', '--if-exists'],
            ['doctrine:database:create'],
            ['doctrine:migrations:migrate', '--no-interaction'],
        ];

        if ($input->getOption('with-content')) {
            $steps[] = ['app:content:import', '--file='.(string) $input->getOption('file')];
        }

        foreach ($steps as $args) {
            $io->section($args[0]);
            $exitCode = $this->runConsole($args);
            if ($exitCode !== 0) {
                $io->error(sprintf('Command "%s" failed with exit code %d.', $args[0], $exitCode));

                return Command::FAILURE;
            }
        }

        $io->success([
            'Local database rebuilt from migrations.',
            'Create an admin (not imported from YAML): php bin/console app:create-admin you@example.com',
        ]);

        return Command::SUCCESS;
    }

    /**
     * @param list<string> $args
     */
    private function runConsole(array $args): int
    {
        $command = array_merge(
            [escapeshellarg(PHP_BINARY), escapeshellarg($this->projectDir.'/bin/console')],
            array_map(escapeshellarg(...), $args),
        );

        $line = implode(' ', $command);
        passthru($line, $exitCode);

        return $exitCode;
    }
}
