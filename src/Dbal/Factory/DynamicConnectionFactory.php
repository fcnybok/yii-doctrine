<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\DoctrineManager;

use function sprintf;

final class DynamicConnectionFactory
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
        private readonly ConnectionFactory $connectionFactory,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @psalm-param array{
     *     auto_commit: bool,
     *     custom_types: array<string,
     *     class-string<\Doctrine\DBAL\Types\Type>>,
     *     events: array<array-key, mixed>,
     *     params: array<string, mixed>,
     *     schema_assets_filter: callable
     * } $dbalConfig
     */
    public function createConnection(array $dbalConfig, string $connectionName): ConnectionModel
    {
        if ($this->doctrineManager->hasConnection($connectionName)) {
            throw new RuntimeException(sprintf('Connection "%s" already exist', $connectionName));
        }

        $connectionModel = $this->connectionFactory->create($dbalConfig, $this->logger);

        $this->doctrineManager->addConnection(
            $connectionName,
            $connectionModel
        );

        return $connectionModel;
    }
}
