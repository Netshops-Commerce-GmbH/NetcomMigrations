<?php

namespace NetcomMigrations\Command;

use NetcomMigrations\Components\Migrations\Executor;
use NetcomMigrations\Components\Migrations\Status;
use NetcomMigrations\Components\Structs\MigrationStruct;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MigrateDownCommand
 */
class MigrateDownCommand extends ShopwareCommand
{
    /** @var SymfonyStyle $io */
    private $io;
    /** @var string $commandName */
    private $commandName;
    /** @var Status $migrationStatus */
    private $migrationStatus;
    /** @var Executor $executor */
    private $executor;

    /**
     * MigrateDownCommand constructor.
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
            ->setDescription('Runs through all pending migrations and executes their "down" method.')
            ->addArgument(
                'rollbackSteps',
                InputArgument::OPTIONAL,
                'Amount of how many migrations shall be rolled back.',
                '1'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $migrations = $this->migrationStatus->getFinishedMigrations();
        $rollbackSteps = $this->getRollbackSteps($input->getArgument('rollbackSteps'));

        if (!\count($migrations)) {
            $this->io->success('No pending migrations found.');

            return;
        }

        $migrations = $this->sliceMigrations($migrations, $rollbackSteps);

        $this->io->writeln('The following migrations will be rolled back:');
        $this->io->listing($this->getMigrationListing($migrations));

        if ($input->isInteractive()
            && !$this->io->confirm(\sprintf('Are you sure to rollback %d migrations?', $rollbackSteps), false)) {
            $this->io->note('Exiting!');

            return;
        }

        $startTime = \microtime(true);

        $this->executor->setIo($this->io);
        $this->executor->migrateDown($migrations);

        $this->io->note(\sprintf('Command runtime: %s seconds.', \microtime(true) - $startTime));
    }

    /**
     * @param string $rollbackSteps
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private function getRollbackSteps(string $rollbackSteps): int
    {
        if (!\is_numeric($rollbackSteps)) {
            throw new InvalidArgumentException('Argument 1 needs to be a numeric value.');
        }

        return (int) $rollbackSteps;
    }

    /**
     * @param MigrationStruct[] $migrations
     * @param int               $rollbackSteps
     *
     * @return array
     */
    private function sliceMigrations(array $migrations, int $rollbackSteps): array
    {
        return \array_slice(
            \array_reverse($migrations),
            0,
            $rollbackSteps
        );
    }

    /**
     * @param MigrationStruct[] $migrations
     *
     * @return array
     */
    private function getMigrationListing(array $migrations): array
    {
        return \array_map(
            function ($migration) {
                /** @var MigrationStruct $migration */

                return '[<fg=red>' . $migration->getVersion() . '</>] <fg=green>' .
                    $migration->getMigration() . '</>';
            },
            $migrations
        );
    }
}
