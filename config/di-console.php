<?php

declare(strict_types=1);

use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Yiisoft\Yii\Doctrine\Dbal\Provider\DbalConnectionProvider;
use Yiisoft\Yii\Doctrine\Fixture\Factory\FixtureFactory;
use Yiisoft\Yii\Doctrine\Fixture\FixtureLoaderManager;
use Yiisoft\Yii\Doctrine\Migrations\Factory\MigrationConfigurationFactory;
use Yiisoft\Yii\Doctrine\Migrations\MigrationConfigurationManager;
use Yiisoft\Yii\Doctrine\Orm\Provider\CustomerEntityManagerProvider;

/** @var array $params */

return [
    ConnectionProvider::class => DbalConnectionProvider::class,

    EntityManagerProvider::class => CustomerEntityManagerProvider::class,

    MigrationConfigurationManager::class => static fn(
        MigrationConfigurationFactory $migrationConfigurationFactory
    ): MigrationConfigurationManager => $migrationConfigurationFactory->create(
        $params['yiisoft/yii-doctrine-migrations'] ?? []
    ),

    FixtureLoaderManager::class => static fn(
        FixtureFactory $fixtureFactory
    ): FixtureLoaderManager => $fixtureFactory->create($params['yiisoft/yii-doctrine-fixture'] ?? []),
];
