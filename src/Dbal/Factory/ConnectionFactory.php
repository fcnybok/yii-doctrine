<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

final class ConnectionFactory
{
    public function __construct(
        private readonly ConfigurationFactory $configurationFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     auto_commit: bool|empty,
     *     custom_types: array<string, class-string<Type>>|empty,
     *     events: array|empty,
     *     middlewares: array<array-key, class-string<Middleware>>|empty,
     *     params: array<string, mixed>,
     *     schema_assets_filter: callable|empty
     * } $dbalConfig
     *
     * @throws Exception
     */
    public function create(array $dbalConfig): Connection
    {
        if (!isset($dbalConfig['params'])) {
            throw new InvalidArgumentException('Not found "params" connection');
        }

        $configuration = $this->configurationFactory->create($dbalConfig);

        $connection = DriverManager::getConnection($dbalConfig['params'], $configuration);

        $this->configureCustomTypes($connection, $dbalConfig['custom_types'] ?? []);

        return $connection;
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
