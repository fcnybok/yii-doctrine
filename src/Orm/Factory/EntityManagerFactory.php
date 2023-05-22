<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Orm\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Psr\Cache\CacheItemPoolInterface;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;
use Yiisoft\Yii\Doctrine\EventManager\EventManagerFactory;

final class EntityManagerFactory
{
    public function __construct(
        private readonly ConfigurationFactory $configurationFactory,
        private readonly EventManagerFactory $eventManagerFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     naming_strategy: class-string|empty,
     *     quote_strategy: class-string|empty,
     *     schema_ignore_classes: list<class-string>|empty,
     *     dql: array{
     *          custom_datetime_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *          custom_numeric_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *          custom_string_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *     }|null,
     *     class_metadata_factory_name: class-string|empty,
     *     default_repository_class: class-string<EntityRepository<object>>|empty,
     *     custom_hydration_modes: array<string, class-string<AbstractHydrator>>|empty,
     *     filters: array<string, class-string<SQLFilter>>|empty,
     *     mappings: array<string, array{dir: string, driver: enum-string, namespace: string, fileExtension: string|empty}>|empty,
     *     events: array|empty,
     *     connection: string
     * } $entityManagerConfig
     * @psalm-param array{auto_generate: bool|null, path: string, namespace: string|null} $proxyConfig
     *
     * @throws ORMException
     */
    public function create(
        ConnectionModel $connectionModel,
        CacheItemPoolInterface $cache,
        array $entityManagerConfig,
        array $proxyConfig
    ): EntityManagerInterface {
        $configuration = $this->configurationFactory->create(
            $cache,
            $entityManagerConfig,
            $proxyConfig
        );

        $this->eventManagerFactory->createForOrm(
            $connectionModel->getEventManager(),
            $entityManagerConfig['events'] ?? []
        );

        return new EntityManager(
            $connectionModel->getConnection(),
            $configuration,
            $connectionModel->getEventManager()
        );
    }
}
