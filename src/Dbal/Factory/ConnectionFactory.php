<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\EventManager\EventManagerFactory;

final class ConnectionFactory
{
    public function __construct(
        private readonly ConfigurationFactory $configurationFactory,
        private readonly EventManagerFactory $eventManagerFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     auto_commit: bool|empty,
     *     custom_types: array<string, class-string<Type>>|empty,
     *     events: array|empty,
     *     middlewares: array<array-key, class-string<\Doctrine\DBAL\Driver\Middleware>>|empty,
     *     params: array<string, mixed>,
     *     schema_assets_filter: callable|empty
     * } $dbalConfig
     *
     * @throws Exception
     */
    public function create(array $dbalConfig): ConnectionModel
    {
        if (!isset($dbalConfig['params'])) {
            throw new InvalidArgumentException('Not found "params" connection');
        }

        $configuration = $this->configurationFactory->create($dbalConfig);

        $eventManager = $this->eventManagerFactory->createForDbal($dbalConfig['events'] ?? []);

        $connection = DriverManager::getConnection($dbalConfig['params'], $configuration, $eventManager);

        $this->configureCustomTypes($connection, $dbalConfig['custom_types'] ?? []);

        return new ConnectionModel($connection, $eventManager);
    }

    /**
     * @psalm-param array<string, class-string<\Doctrine\DBAL\Types\Type>>|empty $customTypes
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
