<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii3 Doctrine Extension</h1>
    <br>
</p>


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/):

```bash
composer require fcnybok/yii-doctrine
```

Basic Usage
-----------

Configuration params doctrine: dbal, orm, migrations, fixture example config path "config/example.php"

Create database:
```bash
php yii doctrine/database/create
```

Drop database:
```bash
php yii doctrine/database/drop --if-exists --force
```

If need default entity manager add on di.php

```php
EntityManagerInterface::class => fn(DoctrineManager $doctrineManager
    ): EntityManagerInterface => $doctrineManager->getManager(
        $params['yiisoft/yii-doctrine']['orm']['default_entity_manager'] ?? DoctrineManager::DEFAULT_ENTITY_MANAGER
    ),
```

Extension use psr6 cache implementation symfony cache.

Options add config on params.php

```php
return [
    'yiisoft/yii-doctrine' => [
        // Used symfony cache
        'cache' => [
            'driver' => CacheAdapterEnum::ARRAY_ADAPTER,
            // only redis or memcached
            'server' => [
                'host' => 'localhost',
                'port' => 6379
            ],
            'namespace' => 'doctrine_',
            // only file cache driver
            'path' => '@runtime/cache/doctrine',
        ],
];
```
Add on di.php
```php
CacheItemPoolInterface::class => fn(CacheFactory $cacheFactory): CacheItemPoolInterface => $cacheFactory->create(
        $params['yiisoft/yii-doctrine']['cache'] ?? []
    ),
```
or add di.php customer implementation

```php
CacheItemPoolInterface::class => new \Symfony\Component\Cache\Adapter\ArrayAdapter(),
```

```php
// $entityManager default connection and setting mapper
final class TestController
{
    public function __construct(
        private readonly \Doctrine\ORM\EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
}

// If two or more entity manager use Yiisoft\Yii\Doctrine\Doctrine\DoctrineManager, find by name entity manager 

class Test2Controller
{
    public function __construct(
        private readonly Yiisoft\Yii\Doctrine\DoctrineManager $doctrineManager,
        private readonly Yiisoft\Yii\Doctrine\Dbal\DynamicConnectionFactory $dynamicConnectionFactory,
        private readonly Yiisoft\Yii\Doctrine\Orm\DynamicEntityManagerFactory $dynamicEntityManagerFactory,
    ) {
    }
    
    public function actionList(): void
    {
        $this->doctrineManager->getEntityManager('postgres');
    }
    
    public function dynamicConnection(): void
    {
        $this->dynamicConnectionFactory->createConnection(
            [
                'params' => [
                    'driver' => 'pdo_mysql',
                    'dbname' => 'test_mysql',
                    'host' => 'localhost',
                    'password' => '',
                    'user' => 'root',
                ],
                'custom_types' => [
                    UuidType::NAME => UuidType::class
                ],
            ],
            'mysql'
        );

        $this->dynamicEntityManagerFactory->create(
            [
                'connection' => 'mysql',
                'mappings' => [
                    'Mysql' => [
                        'dir' => '@common/Mysql',
                        'driver' => DriverMappingEnum::ATTRIBUTE_MAPPING,
                        'namespace' => 'Common\Mysql',
                    ],
                ],
            ],
            [
                'namespace' => 'Proxies',
                'path' => '@runtime/cache/doctrine/proxy',
                'auto_generate' => true
            ],
            'mysql'
        );

        $entityManager = $this->doctrineManager->getManager('mysql');

        $this->doctrineManager->flushAllManager();
    } 
}
```

