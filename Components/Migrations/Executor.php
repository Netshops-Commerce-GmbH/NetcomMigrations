<?php

namespace NetcomMigrations\Components\Migrations;

use NetcomMigrations\Components\Dbal\MigrationsGateway;
use NetcomMigrations\Components\FileTokenizer;
use NetcomMigrations\Components\Structs\MigrationStruct;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Executor
 */
class Executor
{
    /** @var SymfonyStyle $io */
    private $io;

    /** @var FileTokenizer $fileTokenizer */
    private $fileTokenizer;

    /** @var MigrationsGateway $migrationsGateway */
    private $migrationsGateway;

    /** @var ContainerInterface $container */
    private $container;

    /**
     * Executor constructor.
     *
     * @param FileTokenizer      $fileTokenizer
     * @param MigrationsGateway  $migrationsGateway
     * @param ContainerInterface $container
     */
    public function __construct(
        FileTokenizer $fileTokenizer,
        MigrationsGateway $migrationsGateway,
        ContainerInterface $container
    ) {
        $this->fileTokenizer = $fileTokenizer;
        $this->migrationsGateway = $migrationsGateway;
        $this->container = $container;
    }

    /**
     * @param SymfonyStyle $io
     */
    public function setIo(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * @param MigrationStruct[] $migrations
     *
     * @throws \InvalidArgumentException
     */
    public function migrateUp(array $migrations)
    {
        foreach ($migrations as $migration) {
            $this->onBeforeMigrate($migration);

            require_once $migration->getPath();

            $className = $this->fileTokenizer->getClassFromFile($migration->getPath());
            $insertId = $this->migrationsGateway->insert($migration);
            $startTime = \microtime(true);

            /** @var Migration $instance */
            $instance = new $className($this->io);
            $instance->setContainer($this->container);
            $instance->init();
            $instance->up();
            unset($instance);

            $this->migrationsGateway->markFinished($insertId);

            $this->onAfterMigrate($migration, $startTime);
        }

        $this->io->success('Migration finished.');
    }

    /**
     * @param MigrationStruct[] $migrations
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function migrateDown(array $migrations)
    {
        foreach ($migrations as $migration) {
            $this->onBeforeMigrate($migration);

            require_once $migration->getPath();

            $className = $this->fileTokenizer->getClassFromFile($migration->getPath());
            $startTime = \microtime(true);

            /** @var Migration $instance */
            $instance = new $className($this->io);
            $instance->setContainer($this->container);
            $instance->init();
            $instance->down();
            unset($instance);

            $this->migrationsGateway->delete($migration->getVersion(), $migration->getMigration());

            $this->onAfterMigrate($migration, $startTime);
        }

        $this->io->success('Rollback finished.');
    }

    /**
     * @param MigrationStruct $migration
     *
     * @throws \InvalidArgumentException
     */
    protected function onBeforeMigrate(MigrationStruct $migration)
    {
        $this->io->writeln(
            \sprintf(
                '[<fg=red>%s</>] Migrating <fg=green>%s</>...',
                $migration->getVersion(),
                $migration->getMigration()
            )
        );
    }

    /**
     * @param MigrationStruct $migration
     * @param float           $startTime
     *
     * @throws \InvalidArgumentException
     */
    protected function onAfterMigrate(MigrationStruct $migration, float $startTime)
    {
        $this->io->writeln(
            \str_pad(
                '',
                \strlen($migration->getVersion()) + 3,
                ' ',
                STR_PAD_LEFT
            ) .
            \sprintf(
                '<fg=green>Done!</> (%s seconds)',
                \microtime(true) - $startTime
            )
        );
    }
}
