<?php

declare(strict_types=1);

use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Yiisoft\Yii\Doctrine\Dbal\Command\Database\CreateDatabaseCommand;
use Yiisoft\Yii\Doctrine\Dbal\Command\Database\DropDatabaseCommand;
use Yiisoft\Yii\Doctrine\Fixture\Command\FixtureLoadCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\CurrentCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\DiffCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\DumpSchemaCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\ExecuteCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\GenerateCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\LatestCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\ListCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\MigrateCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\RollupCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\StatusCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\SyncMetadataCommand;
use Yiisoft\Yii\Doctrine\Migrations\Command\UpToDateCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            // database
            'doctrine/database/create' => CreateDatabaseCommand::class,
            'doctrine/database/drop' => DropDatabaseCommand::class,
            // dbal
            'doctrine/dbal/run-sql' => RunSqlCommand::class,
            // orm
            'doctrine/orm/info' => InfoCommand::class,
            'doctrine/orm/generate-proxies' => GenerateProxiesCommand::class,
            'doctrine/orm/mapping-describe' => MappingDescribeCommand::class,
            'doctrine/orm/run-dql' => RunDqlCommand::class,
            'doctrine/orm/validate-schema' => ValidateSchemaCommand::class,
            'doctrine/orm/schema-tool/create' => CreateCommand::class,
            'doctrine/orm/schema-tool/drop' => DropCommand::class,
            'doctrine/orm/schema-tool/update' => UpdateCommand::class,
            'doctrine/orm/clear-cache/metadata' => MetadataCommand::class,
            'doctrine/orm/clear-cache/query' => QueryCommand::class,
            'doctrine/orm/clear-cache/result' => ResultCommand::class,
            // migrations
            'doctrine/migrations/current' => CurrentCommand::class,
            'doctrine/migrations/diff' => DiffCommand::class,
            'doctrine/migrations/dump-schema' => DumpSchemaCommand::class,
            'doctrine/migrations/execute' => ExecuteCommand::class,
            'doctrine/migrations/generate' => GenerateCommand::class,
            'doctrine/migrations/latest' => LatestCommand::class,
            'doctrine/migrations/list' => ListCommand::class,
            'doctrine/migrations/migrate' => MigrateCommand::class,
            'doctrine/migrations/rollup' => RollupCommand::class,
            'doctrine/migrations/status' => StatusCommand::class,
            'doctrine/migrations/sync-metadata-storage' => SyncMetadataCommand::class,
            'doctrine/migrations/up-to-date' => UpToDateCommand::class,
            // fixture
            'doctrine/fixture/load' => FixtureLoadCommand::class,
        ],
    ],
    'yiisoft/yii-doctrine' => [],
    'yiisoft/yii-doctrine-migrations' => [],
    'yiisoft/yii-doctrine-fixture' => [],

];
