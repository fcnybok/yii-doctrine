<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use RuntimeException;

use function sprintf;

final class MigrationConfigurationManager
{
    public const DEFAULT_CONFIGURATION = 'default';

    public function __construct(
        /** @psalm-var array<string, Configuration>|array<empty> */
        private readonly array $configurations,
    ) {
    }

    /**
     * @psalm-return Configuration
     */
    public function getConfiguration(string $name = self::DEFAULT_CONFIGURATION): Configuration
    {
        $configuration = $this->configurations[$name] ?? null;

        if (null === $configuration) {
            throw new RuntimeException(sprintf('Not found configuration by name "%s"', $name));
        }

        return $configuration;
    }
}
