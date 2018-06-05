<?php

namespace NetcomMigrations\Command;

use NetcomMigrations\Components\Migrations\Executor;
use NetcomMigrations\Components\Migrations\Status;
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
    protected $io;

    /** @var string $commandName */
    protected $commandName;

    /** @var Status $migrationStatus */
    protected $migrationStatus;

    /** @var Executor $executor */
    protected $executor;

    /**
     * MigrateUpCommand constructor.
     *
     * @param string   $commandName
     * @param Status   $migrationStatus
     * @param Executor $executor
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $commandName, Status $migrationStatus, Executor $executor)
    {
        $this->commandName = $commandName;
        $this->migrationStatus = $migrationStatus;
        $this->executor = $executor;

        parent::__construct($this->commandName);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName($this->commandName)
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
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $migrations = $this->migrationStatus->getPendingMigrations(
            $input->hasArgument('version') ? $input->getArgument('version') : ''
        );

        if (!\count($migrations)) {
            $this->io->success('No pending migrations found.');

            return;
        }

        $startTime = \microtime(true);

        $this->executor->setIo($this->io);
        $this->executor->migrateUp($migrations);

        $this->io->note(\sprintf('Command runtime: %s seconds.', \microtime(true) - $startTime));
    }
}
