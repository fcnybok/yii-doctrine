<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations\Factory;

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Doctrine\Migrations\MigrationConfigurationManager;

final class MigrationConfigurationFactory
{
    public function __construct(
        /**
         * @psalm-var array<string, array{
         *     table_storage: array{
         *          table_name: string|empty,
         *          version_column_name: string|empty,
         *          version_column_length: int|empty,
         *          executed_at_column_name: string|empty
         *     }|empty,
         *     migrations_paths: array<string, string>,
         *     all_or_nothing: bool|empty,
         *     check_database_platform: bool|empty
         * }>
         */
        private array $migrationConfig,
    ) {
    }

    public function __invoke(Aliases $aliases): MigrationConfigurationManager
    {
        $configurations = [];

        if (!empty($this->migrationConfig)) {
            /**
             * @psalm-var array{
             *     table_storage: array{
             *          table_name: string|empty,
             *          version_column_name: string|empty,
             *          version_column_length: int|empty,
             *          executed_at_column_name: string|empty
             *     }|empty,
             *     migrations_paths: array<string, string>,
             *     all_or_nothing: bool|empty,
             *     check_database_platform: bool|empty
             * }|empty $migrationConfig
             */
            foreach ($this->migrationConfig as $configName => $migrationConfig) {
                foreach ($migrationConfig['migrations_paths'] as $namespace => $path) {
                    $this->migrationConfig[$configName]['migrations_paths'][$namespace] = $aliases->get($path);
                }
            }

            foreach ($this->migrationConfig as $configName => $migrationConfig) {
                // create configuration
                $configuration = (new ConfigurationArray($migrationConfig))
                    ->getConfiguration();

                $configurations[$configName] = $configuration;
            }
        }

        return new MigrationConfigurationManager($configurations);
    }
}
