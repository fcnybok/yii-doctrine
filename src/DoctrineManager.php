<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Proxy;
use ReflectionClass;
use RuntimeException;
use Yiisoft\Yii\Doctrine\Dbal\Model\ConnectionModel;

use function array_keys;
use function sprintf;

final class DoctrineManager implements ManagerRegistry
{
    public const DEFAULT_CONNECTION = 'default';
    public const DEFAULT_ENTITY_MANAGER = 'default';

    /** @psalm-var ReflectionClass<object>|class-string */
    private string $proxyInterfaceName = Proxy::class;

    /**
     * @param ConnectionModel[] $connections
     * @param EntityManagerInterface[] $managers
     */
    public function __construct(
        private array $connections,
        private array $managers,
        private readonly string $defaultConnection,
        private readonly string $defaultManager
    ) {
    }

    public function addConnection(string $name, ConnectionModel $connectionModel): void
    {
        if (isset($this->connections[$name])) {
            throw new RuntimeException(sprintf('Connection by name "%s" already exists', $name));
        }

        $this->connections[$name] = $connectionModel;
    }

    public function closeConnection(string $name): void
    {
        $connectionModel = $this->connections[$name] ?? null;

        if (null === $connectionModel) {
            throw new RuntimeException(sprintf('Connection by name "%s" already exists', $name));
        }

        $connectionModel->getConnection()->close();

        unset($this->connections[$name]);
    }

    public function getConnection($name = null): Connection
    {
        if (null === $name) {
            $name = $this->getDefaultConnectionName();
        }

        $connectionModel = $this->connections[$name] ?? null;

        if (null === $connectionModel) {
            throw new RuntimeException(sprintf('Not found connection by name "%s"', $name));
        }

        return $connectionModel->getConnection();
    }

    public function getDefaultConnectionName(): string
    {
        return $this->defaultConnection;
    }

    public function addManager(string $name, EntityManagerInterface $entityManager): void
    {
        if (isset($this->managers[$name])) {
            throw new RuntimeException(sprintf('Entity manager by name "%s" already exists', $name));
        }

        $this->managers[$name] = $entityManager;
    }

    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    public function hasManager(string $name): bool
    {
        return isset($this->managers[$name]);
    }

    public function getConnectionModel(string $name = null): ConnectionModel
    {
        if (null === $name) {
            $name = $this->getDefaultConnectionName();
        }

        $connectionModel = $this->connections[$name] ?? null;

        if (null === $connectionModel) {
            throw new RuntimeException(sprintf('Not found connection by name "%s"', $name));
        }

        return $connectionModel;
    }

    public function getConnections(): array
    {
        $result = [];

        foreach ($this->connections as $name => $connectionModel) {
            $result[$name] = $connectionModel->getConnection();
        }

        return $result;
    }

    /**
     * @psalm-return list<array-key>
     */
    public function getConnectionNames()
    {
        return array_keys($this->connections);
    }

    public function resetManager(?string $name = null)
    {
        if (null === $name) {
            $name = $this->getDefaultManagerName();
        }

        $entityManager = $this->managers[$name] ?? null;

        if (null === $entityManager) {
            throw new RuntimeException(sprintf('Not found entity manager "%s"', $name));
        }

        $entityManager->clear();

        return $entityManager;
    }

    public function getDefaultManagerName(): string
    {
        return $this->defaultManager;
    }

    public function resetAllManager(): void
    {
        foreach ($this->managers as $manager) {
            $manager->clear();
        }
    }

    public function flushManager(?string $name = null): void
    {
        if (null === $name) {
            $name = $this->getDefaultManagerName();
        }

        $entityManager = $this->managers[$name] ?? null;

        if (null === $entityManager) {
            throw new RuntimeException(sprintf('Not found entity manager "%s"', $name));
        }

        $entityManager->flush();
    }

    public function flushAllManager(): void
    {
        foreach ($this->managers as $manager) {
            $manager->flush();
        }
    }

    /**
     * @psalm-return list<array-key>
     */
    public function getManagerNames()
    {
        return array_keys($this->managers);
    }

    public function getRepository($persistentObject, $persistentManagerName = null): EntityRepository|ObjectRepository
    {
        return $this
            ->selectManager($persistentObject, $persistentManagerName)
            ->getRepository($persistentObject);
    }

    /**
     * @psalm-param class-string $persistentObject
     */
    private function selectManager(
        string $persistentObject,
        ?string $persistentManagerName = null
    ): ObjectManager {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObject) ?? $this->getManager();
    }

    /**
     * @param string|null $name
     */
    public function getManager(string $name = null): EntityManagerInterface
    {
        if (null === $name) {
            $name = $this->getDefaultManagerName();
        }

        $entityManager = $this->managers[$name] ?? null;

        if (null === $entityManager) {
            throw new RuntimeException(sprintf('Not found entity manager by name "%s"', $name));
        }

        return $entityManager;
    }

    public function getManagerForClass($class): ?EntityManagerInterface
    {
        $proxyClass = new ReflectionClass($class);

        if ($proxyClass->isAnonymous()) {
            return null;
        }

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            $parentClass = $proxyClass->getParentClass();

            if ($parentClass === false) {
                return null;
            }

            $class = $parentClass->getName();
        }

        foreach ($this->managers as $manager) {
            if (!$manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }

        return null;
    }

    /**
     * @psalm-return array<string, \Doctrine\Persistence\ObjectManager>
     */
    public function getManagers(): array
    {
        return $this->managers;
    }
}
