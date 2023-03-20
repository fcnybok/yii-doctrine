<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Orm\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Yiisoft\Yii\Doctrine\DoctrineManager;

final class CustomerEntityManagerProvider implements EntityManagerProvider
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
    ) {
    }

    public function getDefaultManager(): EntityManagerInterface
    {
        return $this->doctrineManager->getManager();
    }

    public function getManager(string $name): EntityManagerInterface
    {
        return $this->doctrineManager->getManager($name);
    }
}
