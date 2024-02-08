<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Orm\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\QuoteStrategy;
use Doctrine\ORM\Mapping\TypedFieldMapper;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Psr\Cache\CacheItemPoolInterface;
use Yiisoft\Yii\Doctrine\EventManager\EventManagerFactory;
use Yiisoft\Yii\Doctrine\Orm\Enum\DriverMappingEnum;

final class EntityManagerFactory
{
    public function __construct(
        private readonly ConfigurationFactory $configurationFactory,
        private readonly EventManagerFactory $eventManagerFactory,
    ) {
    }

    /**
     * @psalm-param array{
     *     naming_strategy: class-string<NamingStrategy>|empty,
     *     quote_strategy: class-string<QuoteStrategy>|empty,
     *     schema_ignore_classes: list<class-string>|empty,
     *     fetch_mode_sub_select_batch_size: int,
     *     dql: array{
     *          custom_datetime_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *          custom_numeric_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *          custom_string_functions: array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty,
     *     }|null,
     *     class_metadata_factory_name: class-string<AbstractClassMetadataFactory>|empty,
     *     default_repository_class: class-string<EntityRepository<object>>|empty,
     *     repository_factory: class-string<RepositoryFactory>|empty,
     *     custom_hydration_modes: array<string, class-string<AbstractHydrator>>|empty,
     *     filters: array<string, class-string<SQLFilter>>|empty,
     *     entity_listener_resolver: class-string<EntityListenerResolver>|empty,
     *     typed_field_mapper: class-string<TypedFieldMapper>|empty,
     *     default_query_hints: array<string, class-string>|empty,
     *     mappings: array<string, array{
     *          dir: string,
     *          driver: object<DriverMappingEnum>,
     *          namespace: string,
     *          fileExtension: string|empty
     *     }>|empty,
     *     events: array|empty,
     *     connection: string
     * } $entityManagerConfig
     * @psalm-param array{auto_generate: bool|null, path: string, namespace: string|null} $proxyConfig
     *
     */
    public function create(
        Connection $connection,
        CacheItemPoolInterface $cache,
        array $entityManagerConfig,
        array $proxyConfig
    ): EntityManagerInterface {
        $configuration = $this->configurationFactory->create(
            $cache,
            $entityManagerConfig,
            $proxyConfig
        );

        $eventManager = $this->eventManagerFactory->create($entityManagerConfig['events'] ?? []);

        return new EntityManager($connection, $configuration, $eventManager);
    }
}
