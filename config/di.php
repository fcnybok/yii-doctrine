<?php

declare(strict_types=1);

use Yiisoft\Yii\Doctrine\DoctrineManager;
use Yiisoft\Yii\Doctrine\Factory\DoctrineManagerFactory;

/** @var array $params */

return [
    DoctrineManager::class => static fn(
        DoctrineManagerFactory $doctrineManagerFactory
    ): DoctrineManager => $doctrineManagerFactory->create($params['yiisoft/yii-doctrine'] ?? []),
];
