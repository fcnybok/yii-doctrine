<?php

declare(strict_types=1);

use Yiisoft\Yii\Doctrine\DoctrineManager;
use Yiisoft\Yii\Doctrine\Factory\DoctrineManagerFactory;

return [
    DoctrineManager::class => new DoctrineManagerFactory($params['yiisoft/yii-doctrine'] ?? []),
];
