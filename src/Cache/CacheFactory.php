<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Cache;

use InvalidArgumentException;
use Memcached;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Yiisoft\Yii\Doctrine\Cache\Enum\CacheAdapterEnum;

use function extension_loaded;

final class CacheFactory
{
    private const CACHE_NAMESPACE = 'doctrine_';

    /**
     * @psalm-param array{namespace: string|null, driver: string|null, path: string, server: array{host: string, port: int}|null} $cacheConfig
     *
     * @throws CacheException
     */
    public function create(array $cacheConfig): CacheItemPoolInterface
    {
        $cacheNamespace = $cacheConfig['namespace'] ?? self::CACHE_NAMESPACE;

        if (empty($cacheConfig['driver'])) {
            return new NullAdapter();
        }

        switch ($cacheConfig['driver']) {
            case CacheAdapterEnum::ARRAY_ADAPTER:
                $cache = new ArrayAdapter();

                break;
            case CacheAdapterEnum::FILE_ADAPTER:
                $path = $cacheConfig['path'] ?? null;

                if (null === $path) {
                    throw new InvalidArgumentException('Not found path cache dir');
                }

                $cache = new FilesystemAdapter(namespace: $cacheNamespace, directory: $path);

                break;
            case CacheAdapterEnum::REDIS_ADAPTER:
                if (!extension_loaded('redis')) {
                    throw new InvalidArgumentException('Cache provider "redis" don`t load extension');
                }

                $host = $cacheConfig['server']['host'] ?? null;

                if (null === $host) {
                    throw new InvalidArgumentException('Not found redis host');
                }

                $port = $cacheConfig['server']['port'] ?? null;

                if (null === $port) {
                    throw new InvalidArgumentException('Not found redis port');
                }

                $redis = new Redis();
                $redis->connect($host, $port);

                $cache = new RedisAdapter($redis, $cacheNamespace);

                break;
            case CacheAdapterEnum::MEMCACHED_ADAPTER:
                if (!extension_loaded('memcached')) {
                    throw new InvalidArgumentException('Cache provider "memcached" don`t load extension');
                }

                $host = $cacheConfig['server']['host'] ?? null;

                if (null === $host) {
                    throw new InvalidArgumentException('Not found redis host');
                }

                $port = $cacheConfig['server']['port'] ?? null;

                if (null === $port) {
                    throw new InvalidArgumentException('Not found redis port');
                }

                $memcached = new Memcached();
                $memcached->addServer($host, $port);

                $cache = new MemcachedAdapter($memcached, $cacheNamespace);

                break;
            case CacheAdapterEnum::APCU_ADAPTER:
                $cache = new ApcuAdapter($cacheNamespace);

                break;
            default:
                $cache = new NullAdapter();
        }

        return $cache;
    }
}
