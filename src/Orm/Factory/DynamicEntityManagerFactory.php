<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Orm\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Doctrine\ORM\Mapping\TypedFieldMapper;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Yiisoft\Yii\Doctrine\DoctrineManager;

use function sprintf;

final class DynamicEntityManagerFactory
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
        private readonly EntityManagerFactory $entityManagerFactory,
        private readonly CacheItemPoolInterface $cache = new NullAdapter(),
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
     *     entity_listener_resolver: class-string<EntityListenerResolver>|empty,
     *     typed_field_mapper: class-string<TypedFieldMapper>|empty,
     *     mappings: array<string, array{dir: string, driver: enum-string, namespace: string, fileExtension: string|empty}>|empty,
     *     events: array|empty,
     *     connection: string
     * } $entityManagerConfig
     * @psalm-param array{auto_generate: bool|null, path: string, namespace: string|null} $proxyConfig
     */
    public function create(
        array $entityManagerConfig,
        array $proxyConfig,
        string $entityManagerName
    ): EntityManagerInterface {
        if ($this->doctrineManager->hasManager($entityManagerName)) {
            throw new RuntimeException(sprintf('Entity manager "%s" already exist', $entityManagerName));
        }

        $connectionModel = $this->doctrineManager->getConnectionModel($entityManagerConfig['connection']);

        $entityManager = $this->entityManagerFactory->create(
            $connectionModel,
            $this->cache,
            $entityManagerConfig,
            $proxyConfig
        );

        $this->doctrineManager->addManager($entityManagerName, $entityManager);

        return $entityManager;
    }
}
