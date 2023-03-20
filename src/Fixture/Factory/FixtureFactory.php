<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Fixture\Factory;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Doctrine\Fixture\FixtureLoaderManager;

final class FixtureFactory
{
    public function __construct(
        /** @psalm-var array{
         *     entity_managers: array<string, array{
         *          dirs: array<string>|empty,
         *          files: array<string>|empty,
         *          classes: array<class-string<FixtureInterface>>|empty
         *     }>
         *  } $fixtureConfig
         *
         */
        private readonly array $fixtureConfig,
    ) {
    }

    public function __invoke(Aliases $aliases, Injector $injector): FixtureLoaderManager
    {
        $fixtureLoaders = [];

        if (!empty($this->fixtureConfig['entity_managers'])) {
            foreach ($this->fixtureConfig['entity_managers'] as $entityManagerName => $fixtureConfig) {
                $loader = new Loader();

                $this->loadClasses($injector, $loader, $fixtureConfig['classes'] ?? []);
                $this->loadDirs($aliases, $loader, $fixtureConfig['dirs'] ?? []);
                $this->loadFiles($aliases, $loader, $fixtureConfig['files']);

                $fixtureLoaders[$entityManagerName] = $loader;
            }
        }

        return new FixtureLoaderManager($fixtureLoaders);
    }

    /**
     * @psalm-param array<class-string<FixtureInterface>> $classes
     */
    private function loadClasses(Injector $injector, Loader $loader, array $classes): void
    {
        foreach ($classes as $classFixture) {
            $fixture = $injector->make($classFixture);

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
    private function loadDirs(Aliases $aliases, Loader $loader, array $dirs): void
    {
        foreach ($dirs as $dir) {
            $loader->loadFromDirectory($aliases->get($dir));
        }
    }

    /**
     * @psalm-param array<string>|array<empty> $files
     */
    private function loadFiles(Aliases $aliases, Loader $loader, array $files): void
    {
        foreach ($files as $file) {
            $loader->loadFromFile($aliases->get($file));
        }
    }
}
