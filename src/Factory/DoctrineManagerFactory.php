<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Factory;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Yiisoft\Yii\Doctrine\Dbal\Factory\ConnectionFactory;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\DoctrineManager;
use Yiisoft\Yii\Doctrine\Orm\Factory\EntityManagerFactory;

use function sprintf;

final class DoctrineManagerFactory
{
    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly EntityManagerFactory $entityManagerFactory,
        private readonly CacheItemPoolInterface $cacheDriver = new NullAdapter(),
    ) {
    }

    public function create(array $doctrineConfig): DoctrineManager
    {
        // init connections
        $connections = [];

        if (!empty($doctrineConfig['dbal'])) {
            foreach ($doctrineConfig['dbal'] as $name => $dbalConfig) {
                $connections[$name] = $this->connectionFactory->create($dbalConfig);
            }
        }

        // init entity managers
        $entityManagers = [];

        if (!empty($doctrineConfig['orm']['entity_managers'])) {
            foreach ($doctrineConfig['orm']['entity_managers'] as $name => $entityManagerConfig) {
                $connectionName = $entityManagerConfig['connection'] ?? null;

                if (null === $connectionName) {
                    throw new RuntimeException(
                        sprintf('Not found param "connection" on entity manager "%s"', $name)
                    );
                }

                /** @var ConnectionModel|null $connectionModel */
                $connectionModel = $connections[$connectionName] ?? null;

                if (null === $connectionModel) {
                    throw new RuntimeException(
                        sprintf('Not found connection "%s"', $connectionName)
                    );
                }

                $entityManagers[$name] = $this->entityManagerFactory->create(
                    $connectionModel,
                    $this->cacheDriver,
                    $entityManagerConfig,
                    $doctrineConfig['orm']['proxies'] ?? []
                );
            }
        }

        return new DoctrineManager(
            $connections,
            $entityManagers,
            DoctrineManager::DEFAULT_CONNECTION,
            DoctrineManager::DEFAULT_CONNECTION
        );
    }
}
