<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Factory;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Types\Type;
use Yiisoft\Injector\Injector;

final class ConfigurationFactory
{
    public function __construct(
        private readonly Injector $injector,
    ) {
    }

    /**
     * @psalm-param array{
     *     auto_commit: bool,
     *     custom_types: array<string, class-string<Type>>,
     *     events: array<array-key, mixed>,
     *     middlewares: array<array-key, class-string<\Doctrine\DBAL\Driver\Middleware>>|empty,
     *     params: array<string, mixed>,
     *     schema_assets_filter: callable
     * } $dbalConfig
     */
    public function create(array $dbalConfig): Configuration
    {
        $configuration = new Configuration();

        $middlewares = array_map(
            function (string $classMiddleware): Middleware {
                return $this->injector->make($classMiddleware);
            },
            $dbalConfig['middlewares'] ?? []
        );

        $configuration->setMiddlewares($middlewares);

        $this->configureAutoCommit($configuration, $dbalConfig['auto_commit'] ?? null);

        $this->configureSchemaAssetsFilter($configuration, $dbalConfig['schema_assets_filter'] ?? null);

        return $configuration;
    }


    private function configureAutoCommit(Configuration $configuration, ?bool $autoCommit): void
    {
        if (null === $autoCommit) {
            return;
        }

        $configuration->setAutoCommit($autoCommit);
    }

    private function configureSchemaAssetsFilter(Configuration $configuration, ?callable $schemaAssetsFilter): void
    {
        if (null === $schemaAssetsFilter) {
            return;
        }

        $configuration->setSchemaAssetsFilter($schemaAssetsFilter);
    }
}
