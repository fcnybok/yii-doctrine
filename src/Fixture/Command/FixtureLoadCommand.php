<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Fixture\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Doctrine\DoctrineManager;
use Yiisoft\Yii\Doctrine\Fixture\FixtureLoaderManager;

final class FixtureLoadCommand extends Command
{
    public function __construct(
        private readonly DoctrineManager $doctrineManager,
        private readonly FixtureLoaderManager $fixtureLoaderManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('doctrine:fixture:load')
            ->setDescription('Load fixture')
            ->addOption('em', 'e', InputOption::VALUE_REQUIRED, 'Entity manager name')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command load fixture:

You can also optionally specify the name of the entity manager:

    <info>php %command.full_name% --em=default</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $emName */
        $emName = $input->getOption('em');

        $loader = $this->fixtureLoaderManager->getLoader($emName);

        $entityManager = $this->doctrineManager->getManager($emName);

        $executor = new ORMExecutor($entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());

        $output->writeln(sprintf('<info>Load fixture for entity manager "%s"</info>', $emName));

        return ExitCode::OK;
    }
}
