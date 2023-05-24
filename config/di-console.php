<?php

declare(strict_types=1);

use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\Doctrine\Dbal\Provider\DbalConnectionProvider;
use Yiisoft\Yii\Doctrine\Fixture\Factory\FixtureFactory;
use Yiisoft\Yii\Doctrine\Fixture\FixtureLoaderManager;
use Yiisoft\Yii\Doctrine\Migrations\Factory\MigrationConfigurationFactory;
use Yiisoft\Yii\Doctrine\Migrations\MigrationConfigurationManager;
use Yiisoft\Yii\Doctrine\Orm\Provider\CustomerEntityManagerProvider;

/** @var $params */

return [
    ConnectionProvider::class => Reference::to(DbalConnectionProvider::class),

    EntityManagerProvider::class => Reference::to(CustomerEntityManagerProvider::class),

    MigrationConfigurationManager::class => new MigrationConfigurationFactory(
        $params['yiisoft/yii-doctrine-migrations'] ?? []
    ),

    FixtureLoaderManager::class => new FixtureFactory($params['yiisoft/yii-doctrine-fixture'] ?? []),
];
