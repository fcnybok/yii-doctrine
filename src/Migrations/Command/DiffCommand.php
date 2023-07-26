<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations\Command;

use Doctrine\Migrations\Generator\Exception\NoChangesDetected;
use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Metadata\ExecutedMigrationsList;
use Doctrine\Migrations\Tools\Console\Exception\InvalidOptionUsage;
use OutOfBoundsException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

use function addslashes;
use function assert;
use function count;
use function filter_var;
use function is_string;
use function key;
use function sprintf;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * The DiffCommand class is responsible for generating a migration by comparing your current database schema to
 * your mapping information.
 */
final class DiffCommand extends BaseMigrationCommand
{
    protected function configure(): void
    {
        $this
            ->setName('doctrine:migrations:diff')
            ->setAliases(['diff'])
            ->setDescription('Generate a migration by comparing your current database to your mapping information.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command generates a migration by comparing your current database to your mapping information:

    <info>%command.full_name%</info>

EOT
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)'
            )
            ->addOption(
                'filter-expression',
                null,
                InputOption::VALUE_REQUIRED,
                'Tables which are filtered by Regular Expression.'
            )
            ->addOption(
                'line-length',
                null,
                InputOption::VALUE_REQUIRED,
                'Max line length of unformatted lines.',
                '120'
            )
            ->addOption(
                'check-database-platform',
                null,
                InputOption::VALUE_OPTIONAL,
                'Check Database Platform to the generated code.',
                false
            )
            ->addOption(
                'allow-empty-diff',
                null,
                InputOption::VALUE_NONE,
                'Do not throw an exception when no changes are detected.'
            )
            ->addOption(
                'from-empty-schema',
                null,
                InputOption::VALUE_NONE,
                'Generate a full migration as if the current database was empty.'
            );

        parent::configure();
    }

    /**
     * @throws InvalidOptionUsage
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $filterExpression = (string)$input->getOption('filter-expression');

        if ($filterExpression === '') {
            $filterExpression = null;
        }

        $lineLength = (int)$input->getOption('line-length');
        /** @var bool $allowEmptyDiff */
        $allowEmptyDiff = $input->getOption('allow-empty-diff');
        $checkDbPlatform = filter_var($input->getOption('check-database-platform'), FILTER_VALIDATE_BOOLEAN);
        /** @var bool $fromEmptySchema */
        $fromEmptySchema = $input->getOption('from-empty-schema');
        /** @var string $namespace */
        $namespace = $input->getOption('namespace');

        if ($namespace === '') {
            $namespace = null;
        }

        $configuration = $this
            ->getDependencyFactory()
            ->getConfiguration();

        $dirs = $configuration->getMigrationDirectories();

        if ($namespace === null) {
            $namespace = key($dirs);
        } elseif (!isset($dirs[$namespace])) {
            throw new OutOfBoundsException(sprintf('Path not defined for the namespace %s', $namespace));
        }

        assert(is_string($namespace));

        $statusCalculator = $this
            ->getDependencyFactory()
            ->getMigrationStatusCalculator();

        $executedUnavailableMigrations = $statusCalculator->getExecutedUnavailableMigrations();
        $newMigrations = $statusCalculator->getNewMigrations();

        if (!$this->checkNewMigrationsOrExecutedUnavailable(
            $newMigrations,
            $executedUnavailableMigrations,
            $input
        )) {
            $this->io->error('Migration cancelled!');

            return 3;
        }

        $fqcn = $this
            ->getDependencyFactory()
            ->getClassNameGenerator()
            ->generateClassName($namespace);

        $diffGenerator = $this
            ->getDependencyFactory()
            ->getDiffGenerator();

        try {
            $path = $diffGenerator->generate(
                $fqcn,
                $filterExpression,
                false,
                $lineLength,
                $checkDbPlatform,
                $fromEmptySchema
            );
        } catch (NoChangesDetected $exception) {
            if ($allowEmptyDiff) {
                $this->io->error($exception->getMessage());

                return ExitCode::OK;
            }

            throw $exception;
        }

        $this->io->text(
            [
                sprintf('Generated new migration class to "<info>%s</info>"', $path),
                '',
                sprintf(
                    'To run just this migration for testing purposes, you can use <info>doctrine:migrations:execute --up \'%s\'</info>',
                    addslashes($fqcn)
                ),
                '',
                sprintf(
                    'To revert the migration you can use <info>doctrine:migrations:execute --down \'%s\'</info>',
                    addslashes($fqcn)
                ),
                '',
            ]
        );

        return ExitCode::OK;
    }

    private function checkNewMigrationsOrExecutedUnavailable(
        AvailableMigrationsList $newMigrations,
        ExecutedMigrationsList $executedUnavailableMigrations,
        InputInterface $input
    ): bool {
        if (count($newMigrations) === 0 && count($executedUnavailableMigrations) === 0) {
            return true;
        }

        if (count($newMigrations) !== 0) {
            $this->io->warning(
                sprintf(
                    'You have %d available migrations to execute.',
                    count($newMigrations)
                )
            );
        }

        if (count($executedUnavailableMigrations) !== 0) {
            $this->io->warning(
                sprintf(
                    'You have %d previously executed migrations in the database that are not registered migrations.',
                    count($executedUnavailableMigrations)
                )
            );
        }

        return $this->canExecute('Are you sure you wish to continue?', $input);
    }
}
