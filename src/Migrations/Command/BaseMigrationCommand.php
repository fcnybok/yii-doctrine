<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations\Command;

use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Exception\DependenciesNotSatisfied;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Doctrine\DoctrineManager;
use Yiisoft\Yii\Doctrine\Migrations\MigrationConfigurationManager;

class BaseMigrationCommand extends Command
{
    protected ?DependencyFactory $dependencyFactory = null;

    protected ?SymfonyStyle $io = null;

    public function __construct(
        private readonly DoctrineManager $doctrineManager,
        private readonly MigrationConfigurationManager $migrationConfigurationManager,
        private readonly LoggerInterface $logger = new NullLogger(),
        string $name = null,
    ) {
        parent::__construct($name);
    }


    protected function configure(): void
    {
        $this->addOption(
            'configuration',
            null,
            InputOption::VALUE_OPTIONAL,
            'The name to a migrations configuration on config.'
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        /** @var string $configurationName */
        $configurationName = $input->getOption('configuration') ?? MigrationConfigurationManager::DEFAULT_CONFIGURATION;

        $configuration = $this->migrationConfigurationManager->getConfiguration($configurationName);

        $configurationLoader = new ExistingConfiguration($configuration);

        if (null !== $configuration->getConnectionName() && null === $configuration->getEntityManagerName()) {
            $connectionLoader = ConnectionRegistryConnection::withSimpleDefault($this->doctrineManager);

            $this->dependencyFactory = DependencyFactory::fromConnection(
                $configurationLoader,
                $connectionLoader,
                $this->logger
            );
        } elseif (null === $configuration->getConnectionName() && null !== $configuration->getEntityManagerName()) {
            $entityManagerLoader = ManagerRegistryEntityManager::withSimpleDefault($this->doctrineManager);

            $this->dependencyFactory = DependencyFactory::fromEntityManager(
                $configurationLoader,
                $entityManagerLoader,
                $this->logger
            );
        } else {
            throw new RuntimeException('Error config');
        }

        if ($this->dependencyFactory->isFrozen()) {
            return;
        }

        $this->dependencyFactory->freeze();
    }

    protected function getDependencyFactory(): DependencyFactory
    {
        if ($this->dependencyFactory === null) {
            throw DependenciesNotSatisfied::new();
        }

        return $this->dependencyFactory;
    }

    protected function canExecute(string $question, InputInterface $input): bool
    {
        return !$input->isInteractive() || $this->io->confirm($question);
    }
}
