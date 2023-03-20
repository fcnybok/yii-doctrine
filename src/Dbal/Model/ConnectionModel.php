<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Dbal\Model;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;

final class ConnectionModel
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Configuration $configuration,
        private readonly EventManager $eventManager
    ) {
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getEventManager(): EventManager
    {
        return $this->eventManager;
    }
}
