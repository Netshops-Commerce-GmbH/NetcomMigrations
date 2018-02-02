<?php

namespace NetcomMigrations\Command;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MigrateUpCommand
 */
class MigrateUpCommand extends ShopwareCommand
{
    /** @var SymfonyStyle $io */
    private $io;

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('netcom:migrations:migrate:up')
            ->setDescription('Runs through all pending migrations and executes their "up" method.')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Specific version that will be migrated only!'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $migrations = $this->container->get('netcom_migrations.components.migrations.status')->getPendingMigrations(
            $input->hasArgument('version') ? $input->getArgument('version') : ''
        );

        if (!\count($migrations)) {
            $this->io->success('No pending migrations found.');

            return;
        }

        $startTime = \microtime(true);

        $executor = $this->container->get('netcom_migrations.components.migrations.executor');
        $executor->setIo($this->io);
        $executor->migrateUp($migrations);

        $this->io->note(\sprintf('Command runtime: %s seconds.', \microtime(true) - $startTime));
    }
}
