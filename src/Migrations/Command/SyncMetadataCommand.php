<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Migrations\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

final class SyncMetadataCommand extends BaseMigrationCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->getDependencyFactory()
            ->getMetadataStorage()
            ->ensureInitialized();

        $this->io->success('Metadata storage synchronized');

        return ExitCode::OK;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('doctrine/migrations/sync-metadata-storage')
            ->setAliases(['sync-metadata-storage'])
            ->setDescription('Ensures that the metadata storage is at the latest version.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command updates metadata storage the latest version.

    <info>%command.full_name%</info>
EOT
            );
    }
}
