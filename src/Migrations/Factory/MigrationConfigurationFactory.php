<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations\Factory;

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Doctrine\Migrations\MigrationConfigurationManager;

final class MigrationConfigurationFactory
{
    public function __construct(
        private readonly Aliases $aliases,
    ) {
    }

    /**
     * @psalm-param array<string, array{
     *     table_storage: array{
     *          table_name: string|empty,
     *          version_column_name: string|empty,
     *          version_column_length: int|empty,
     *          executed_at_column_name: string|empty
     *     }|empty,
     *     migrations_paths: array<string, string>,
     *     all_or_nothing: bool|empty,
     *     check_database_platform: bool|empty
     * }> $migrationConfig
     */
    public function create(array $migrationConfig): MigrationConfigurationManager
    {
        $configurations = [];

        if (!empty($migrationConfig)) {
            foreach ($migrationConfig as $configName => $config) {
                foreach ($config['migrations_paths'] as $namespace => $path) {
                    $migrationConfig[$configName]['migrations_paths'][$namespace] = $this->aliases->get($path);
                }
            }

            foreach ($migrationConfig as $configName => $config) {
                // create configuration
                $configuration = (new ConfigurationArray($config))
                    ->getConfiguration();

                $configurations[$configName] = $configuration;
            }
        }

        return new MigrationConfigurationManager($configurations);
    }
}
