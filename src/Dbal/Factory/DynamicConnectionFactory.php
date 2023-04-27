<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use RuntimeException;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\DoctrineManager;

use function sprintf;

final class DynamicConnectionFactory
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
        private readonly ConnectionFactory $connectionFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     auto_commit: bool,
     *     custom_types: array<string, class-string<\Doctrine\DBAL\Types\Type>>,
     *     events: array<array-key, mixed>,
     *     middlewares: array<array-key, class-string<\Doctrine\DBAL\Driver\Middleware>>|empty,
     *     params: array<string, mixed>,
     *     schema_assets_filter: callable
     * } $dbalConfig
     */
    public function createConnection(array $dbalConfig, string $connectionName): ConnectionModel
    {
        if ($this->doctrineManager->hasConnection($connectionName)) {
            throw new RuntimeException(sprintf('Connection "%s" already exist', $connectionName));
        }

        $connectionModel = $this->connectionFactory->create($dbalConfig);

        $this->doctrineManager->addConnection(
            $connectionName,
            $connectionModel
        );

        return $connectionModel;
    }
}
