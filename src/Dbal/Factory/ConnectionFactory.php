<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Yiisoft\Yii\Doctrine\Configuration\ConfigurationBuilder;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\EventManager\EventManagerFactory;

final class ConnectionFactory
{
    public function __construct(
        private readonly ConfigurationBuilder $configurationBuilder,
        private readonly EventManagerFactory $eventManagerFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     params: array<string, mixed>,
     *     events: array|empty,
     *     custom_types: array<string, class-string<Type>>|empty,
     *     auto_commit: bool|empty,
     *     schema_assets_filter: callable|empty
     * } $dbalConfig
     *
     * @throws Exception
     */
    public function create(array $dbalConfig, LoggerInterface $logger): ConnectionModel
    {
        if (!isset($dbalConfig['params'])) {
            throw new InvalidArgumentException('Not found "params" connection');
        }

        $configuration = $this->configurationBuilder->createConfigurationDbal($dbalConfig, $logger);

        $eventManager = $this->eventManagerFactory->createForDbal($dbalConfig['events'] ?? []);

        $connection = DriverManager::getConnection($dbalConfig['params'], $configuration, $eventManager);

        $this->configureCustomTypes($connection, $dbalConfig['custom_types'] ?? []);

        return new ConnectionModel($connection, $configuration, $eventManager);
    }

    /**
     * @psalm-param array<string, class-string<Type>>|empty $customTypes
     *
     * @throws Exception
     */
    private function configureCustomTypes(Connection $connection, array $customTypes): void
    {
        foreach ($customTypes as $name => $className) {
            if (!Type::hasType($name)) {
                Type::addType($name, $className);
            }

            $connection->getDatabasePlatform()->registerDoctrineTypeMapping($name, $name);
        }
    }
}
