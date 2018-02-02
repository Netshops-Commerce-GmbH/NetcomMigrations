<?php

namespace NetcomMigrations\Command;

use NetcomMigrations\Components\Migrations\Status;
use NetcomMigrations\Components\Structs\MigrationStruct;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 */
class StatusCommand extends ShopwareCommand
{
    /** @var SymfonyStyle $io */
    protected $io;
    /** @var string $commandName */
    private $commandName;
    /** @var Status $migrationStatus */
    private $migrationStatus;

    /**
     * StatusCommand constructor.
     *
     * @param string $commandName
     * @param Status $migrationStatus
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $commandName, Status $migrationStatus)
    {
        $this->commandName = $commandName;
        $this->migrationStatus = $migrationStatus;

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
            ->setDescription('Shows the status of all migrations.');
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pendingMigrationsTableData = $this->getPendingMigrations();
        $finishedMigrationsTableData = $this->getFinishedMigrations();

        $this->io = new SymfonyStyle($input, $output);
        $this->io->writeln("\n<comment>Pending migrations (in order which they would be executed):</>");

        if (!\count($pendingMigrationsTableData)) {
            $this->io->success('No pending migrations found.');
        } else {
            $this->io->table(\array_keys(\reset($pendingMigrationsTableData)), $pendingMigrationsTableData);
        }

        $this->io->writeln('<comment>Finished migrations (in order which they have been exectued):</>');

        if (!\count($finishedMigrationsTableData)) {
            $this->io->success('No finished migrations found.');
        } else {
            $this->io->table(\array_keys(\reset($finishedMigrationsTableData)), $finishedMigrationsTableData);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getPendingMigrations(): array
    {
        $migrations = [];

        foreach ($this->migrationStatus->getPendingMigrations() as $migration) {
            $migrations[] = $this->createTableDataFromMigrationStruct($migration);
        }

        return $migrations;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getFinishedMigrations(): array
    {
        $migrations = [];

        foreach ($this->migrationStatus->getFinishedMigrations() as $migration) {
            $migrations[] = $this->createTableDataFromMigrationStruct($migration);
        }

        return $migrations;
    }

    /**
     * @param MigrationStruct $migration
     *
     * @return array
     */
    private function createTableDataFromMigrationStruct(MigrationStruct $migration): array
    {
        $startDate = $migration->getStartDate();
        $finishDate = $migration->getFinishDate();

        return [
            'status'     => $migration->isPending() ? 'pending' : 'finished',
            'version'    => $migration->getVersion(),
            'migration'  => $migration->getMigration(),
            'startDate'  => $startDate instanceof \DateTime ? $startDate->format('Y-m-d H:i:s') : '',
            'finishDate' => $finishDate instanceof \DateTime ? $finishDate->format('Y-m-d H:i:s') : '',
        ];
    }
}
