<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Fixture\Factory;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Doctrine\Fixture\FixtureLoaderManager;

use function sprintf;

final class FixtureFactory
{
    public function __construct(
        private readonly Aliases $aliases,
        private readonly Injector $injector,
    ) {
    }

    /**
     * @psalm-param array{
     *     entity_managers: array<string, array{
     *          dirs: array<string>|empty,
     *          files: array<string>|empty,
     *          classes: array<class-string<FixtureInterface>>|empty
     *     }>
     *  } $fixtureConfig
     */
    public function create(array $fixtureConfig): FixtureLoaderManager
    {
        $fixtureLoaders = [];

        if (!empty($fixtureConfig['entity_managers'])) {
            foreach ($fixtureConfig['entity_managers'] as $entityManagerName => $config) {
                $loader = new Loader();

                $this->loadClasses($loader, $config['classes'] ?? []);
                $this->loadDirs($loader, $config['dirs'] ?? []);
                $this->loadFiles($loader, $config['files']);

                $fixtureLoaders[$entityManagerName] = $loader;
            }
        }

        return new FixtureLoaderManager($fixtureLoaders);
    }

    /**
     * @psalm-param array<class-string<FixtureInterface>> $classes
     */
    private function loadClasses(Loader $loader, array $classes): void
    {
        foreach ($classes as $classFixture) {
            $fixture = $this->injector->make($classFixture);

            if (!$fixture instanceof FixtureInterface) {
                throw new RuntimeException(
                    sprintf('Class %s not instanceof %s', $classFixture, FixtureInterface::class)
                );
            }

            $loader->addFixture($fixture);
        }
    }

    /**
     * @psalm-param array<string>|array<empty> $dirs
     */
    private function loadDirs(Loader $loader, array $dirs): void
    {
        foreach ($dirs as $dir) {
            $loader->loadFromDirectory($this->aliases->get($dir));
        }
    }

    /**
     * @psalm-param array<string>|array<empty> $files
     */
    private function loadFiles(Loader $loader, array $files): void
    {
        foreach ($files as $file) {
            $loader->loadFromFile($this->aliases->get($file));
        }
    }
}
