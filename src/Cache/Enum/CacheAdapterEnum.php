<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Cache\Enum;

enum CacheAdapterEnum: string
{
    case APCU_ADAPTER = 'apcu';
    case ARRAY_ADAPTER = 'array';
    case FILE_ADAPTER = 'file';
    case MEMCACHED_ADAPTER = 'memcached';
    case REDIS_ADAPTER = 'redis';
}
