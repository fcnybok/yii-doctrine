<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Yiisoft\Yii\Doctrine\DoctrineManager;

final class DbalConnectionProvider implements ConnectionProvider
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
    ) {
    }

    public function getDefaultConnection(): Connection
    {
        return $this->doctrineManager->getConnection();
    }

    public function getConnection(string $name): Connection
    {
        return $this->doctrineManager->getConnection($name);
    }
}
