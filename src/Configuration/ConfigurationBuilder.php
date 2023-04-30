<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Configuration;

use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\QuoteStrategy;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Doctrine\Orm\Enum\DriverMappingEnum;

use function sprintf;

final class ConfigurationBuilder
{
    public function __construct(
        private readonly Aliases $aliases,
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
    public function createConfigurationDbal(array $dbalConfig): Configuration
    {
        $configuration = new Configuration();

        // logger
        $middlewares = array_map(
            function ($middleware): Middleware {
                return $this->injector->make($middleware);
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
        if (null !== $autoCommit) {
            $configuration->setAutoCommit($autoCommit);
        }
    }

    private function configureSchemaAssetsFilter(Configuration $configuration, ?callable $schemaAssetsFilter): void
    {
        if (null !== $schemaAssetsFilter) {
            $configuration->setSchemaAssetsFilter($schemaAssetsFilter);
        }
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
     * } $ormConfig
     * @psalm-param array{auto_generate: bool|null, path: string, namespace: string|null} $proxyConfig
     */
    public function configureOrm(
        Configuration $configuration,
        CacheItemPoolInterface $cacheDriver,
        array $ormConfig,
        array $proxyConfig
    ): void {
        // naming strategy
        $this->configureNamingStrategy($configuration, $ormConfig['naming_strategy'] ?? null);
        // quote strategy
        $this->configureQuoteStrategy($configuration, $ormConfig['quote_strategy'] ?? null);

        // orm cache
        $configuration->setMetadataCache($cacheDriver);
        $configuration->setQueryCache($cacheDriver);
        $configuration->setHydrationCache($cacheDriver);
        $configuration->setResultCache($cacheDriver);

        // proxy
        $configuration->setProxyDir(
            $this->getProxyDir($proxyConfig['path'] ?? null)
        );
        $configuration->setProxyNamespace(
            $this->getProxyName($proxyConfig['namespace'] ?? 'Proxy')
        );
        $configuration->setAutoGenerateProxyClasses($proxyConfig['auto_generate'] ?? true);

        $this->configureSchemaIgnoreClasses($configuration, $ormConfig['schema_ignore_classes'] ?? []);

        // init custom datetime function
        $this->configureCustomDatetimeFunctions(
            $configuration,
            $ormConfig['dql']['custom_datetime_functions'] ?? []
        );
        // init custom numeric function
        $this->configureCustomNumericFunctions(
            $configuration,
            $ormConfig['dql']['custom_numeric_functions'] ?? []
        );
        // init custom string function
        $this->configureCustomStringFunctions(
            $configuration,
            $ormConfig['dql']['custom_string_functions'] ?? []
        );

        $this->configureClassMetadataFactoryName($configuration, $ormConfig['class_metadata_factory_name'] ?? null);

        $this->configureDefaultRepositoryClass($configuration, $ormConfig['default_repository_class'] ?? null);

        // init custom hydration modes
        $this->configureCustomHydrationModes($configuration, $ormConfig['custom_hydration_modes'] ?? []);

        // init filters
        $this->configureFilters($configuration, $ormConfig['filters'] ?? []);

        // init meta data drivers
        $this->configureMetaDataDrivers($configuration, $ormConfig['mappings'] ?? []);
    }

    /**
     * @psalm-param class-string|null $className
     */
    private function configureNamingStrategy(Configuration $configuration, ?string $className): void
    {
        if (null === $className) {
            return;
        }

        $namingStrategy = $this->injector->make($className);

        if (!$namingStrategy instanceof NamingStrategy) {
            throw new RuntimeException(sprintf('Class %s not instanceof %s', $className, NamingStrategy::class));
        }

        $configuration->setNamingStrategy($namingStrategy);
    }

    /**
     * @psalm-param class-string|null $className
     */
    private function configureQuoteStrategy(Configuration $configuration, ?string $className): void
    {
        if (null === $className) {
            return;
        }

        $quoteStrategy = $this->injector->make($className);

        if (!$quoteStrategy instanceof QuoteStrategy) {
            throw new RuntimeException(sprintf('Class %s not instanceof %s', $className, QuoteStrategy::class));
        }

        $configuration->setQuoteStrategy($quoteStrategy);
    }

    private function getProxyDir(?string $proxyPath): string
    {
        if (null === $proxyPath) {
            throw new RuntimeException('Not found path proxies');
        }

        return $this->aliases->get($proxyPath);
    }

    private function getProxyName(?string $namespace): string
    {
        if (null === $namespace) {
            throw new InvalidArgumentException('Not found proxies namespace');
        }

        return $namespace;
    }

    /**
     * @param list<class-string> $schemaIgnoreClasses
     */
    private function configureSchemaIgnoreClasses(Configuration $configuration, array $schemaIgnoreClasses): void
    {
        if (count($schemaIgnoreClasses) > 0) {
            $configuration->setSchemaIgnoreClasses($schemaIgnoreClasses);
        }
    }

    /**
     * @psalm-param array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty $customDatetimeFunctions
     */
    private function configureCustomDatetimeFunctions(
        Configuration $configuration,
        array $customDatetimeFunctions
    ): void {
        foreach ($customDatetimeFunctions as $name => $className) {
            $configuration->addCustomDatetimeFunction($name, $className);
        }
    }

    /**
     * @psalm-param array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty $customNumericFunctions
     */
    private function configureCustomNumericFunctions(
        Configuration $configuration,
        array $customNumericFunctions
    ): void {
        foreach ($customNumericFunctions as $name => $className) {
            $configuration->addCustomNumericFunction($name, $className);
        }
    }

    /**
     * @psalm-param array<string, callable(string):FunctionNode|class-string<FunctionNode>>|empty $customStringFunctions
     */
    private function configureCustomStringFunctions(
        Configuration $configuration,
        array $customStringFunctions
    ): void {
        foreach ($customStringFunctions as $name => $className) {
            $configuration->addCustomStringFunction($name, $className);
        }
    }

    /**
     * @psalm-param class-string|null $classMetadataFactoryName
     */
    private function configureClassMetadataFactoryName(
        Configuration $configuration,
        ?string $classMetadataFactoryName
    ): void {
        if (null !== $classMetadataFactoryName) {
            $configuration->setClassMetadataFactoryName($classMetadataFactoryName);
        }
    }

    /**
     * @psalm-param class-string<EntityRepository> $defaultRepositoryClass
     */
    private function configureDefaultRepositoryClass(
        Configuration $configuration,
        ?string $defaultRepositoryClass
    ): void {
        if (null !== $defaultRepositoryClass) {
            $configuration->setDefaultRepositoryClassName($defaultRepositoryClass);
        }
    }

    /**
     * @psalm-param array<string, class-string<AbstractHydrator>>|empty $customHydrationModes
     */
    private function configureCustomHydrationModes(Configuration $configuration, array $customHydrationModes): void
    {
        foreach ($customHydrationModes as $name => $className) {
            $configuration->addCustomHydrationMode($name, $className);
        }
    }

    /**
     * @psalm-param  array<string, class-string<SQLFilter>>|empty $filters
     */
    private function configureFilters(Configuration $configuration, array $filters): void
    {
        foreach ($filters as $name => $className) {
            $configuration->addFilter($name, $className);
        }
    }

    /**
     * @psalm-param array<string, array{dir: string, driver: enum-string, namespace: string, fileExtension: string|empty}>|empty $mappings
     */
    private function configureMetaDataDrivers(Configuration $configuration, array $mappings): void
    {
        $driverChain = new MappingDriverChain();

        foreach ($mappings as $name => $mapper) {
            if (!isset($mapper['driver'])) {
                throw new InvalidArgumentException('Not found "driver" mapping');
            }

            if (!isset($mapper['dir'])) {
                throw new InvalidArgumentException('Not found "directory" mapping');
            }

            if (!isset($mapper['namespace'])) {
                throw new InvalidArgumentException('Not found "namespace" mapping');
            }

            $dir = $this->aliases->get($mapper['dir']);

            switch ($mapper['driver']) {
                case DriverMappingEnum::XML_MAPPING:
                    $driver = new SimplifiedXmlDriver(
                        [
                            $dir => $mapper['namespace']
                        ],
                        $mapper['fileExtension'] ?? SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION
                    );

                    break;
                case DriverMappingEnum::ATTRIBUTE_MAPPING:
                    if (PHP_VERSION_ID < 80000) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Driver mapping "%s" only version php-8',
                                DriverMappingEnum::ATTRIBUTE_MAPPING->value
                            )
                        );
                    }

                    $driver = new AttributeDriver([$dir]);

                    break;
                case DriverMappingEnum::PHP_MAPPING:
                    $driver = new PHPDriver($dir);

                    break;
                case DriverMappingEnum::STATIC_PHP_MAPPING:
                    $driver = new StaticPHPDriver($dir);

                    break;
                default:
                    throw new InvalidArgumentException(
                        'Doctrine driver mapper: "attribute", "php", "static_php", "xml" not found'
                    );
            }

            $driverChain->addDriver($driver, $mapper['namespace']);
        }

        $configuration->setMetadataDriverImpl($driverChain);
    }
}
