<?php

namespace NetcomMigrations\Components\Migrations;

use NetcomMigrations\Components\Dbal\MigrationsGateway;
use NetcomMigrations\Components\Structs\MigrationStruct;

/**
 * Class Status
 */
class Status
{
    /** @var MigrationsGateway $migrationsGateway */
    private $migrationsGateway;

    /** @var string $migrationsDir */
    private $migrationsDir;

    /**
     * Status constructor.
     *
     * @param MigrationsGateway $migrationsGateway
     * @param string            $migrationsDir
     */
    public function __construct(MigrationsGateway $migrationsGateway, string $migrationsDir)
    {
        $this->migrationsGateway = $migrationsGateway;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * @param string $version
     *
     * @return MigrationStruct[]
     */
    public function getPendingMigrations($version = ''): array
    {
        return \array_filter(
            $this->getAllMigrations(),
            function ($migration) use ($version) {
                /** @var MigrationStruct $migration */
                if (!empty($version)) {
                    return $version === $migration->getVersion() && $migration->isPending();
                }

                return $migration->isPending();
            }
        );
    }

    /**
     * @return MigrationStruct[]
     */
    public function getFinishedMigrations(): array
    {
        $migrations = \array_filter(
            $this->getAllMigrations(),
            function ($migration) {
                /** @var MigrationStruct $migration */

                return $migration->isFinished();
            }
        );

        \usort(
            $migrations,
            function ($a, $b) {
                /** @var MigrationStruct $a */
                /** @var MigrationStruct $b */

                return $a->getStartDate() <=> $b->getStartDate();
            }
        );

        return $migrations;
    }

    /**
     * @return array
     */
    public function getAllMigrations(): array
    {
        $paths = \glob($this->migrationsDir . '/**/*.php');

        if (!\count($paths)) {
            return [];
        }

        return $this->buildMigrationStructs($paths);
    }

    /**
     * @param array $paths
     *
     * @return MigrationStruct[]
     */
    private function buildMigrationStructs(array $paths): array
    {
        /** @var MigrationStruct[] $processedMigrations */
        $processedMigrations = $this->migrationsGateway->getProcessedMigrations();
        $migrationStructs = [];

        foreach ($paths as $path) {
            $migrationStructs[] = $this->buildMigrationStruct($path, $processedMigrations);
        }

        return $migrationStructs;
    }

    /**
     * @param string $path
     * @param array  $processedMigrations
     *
     * @return MigrationStruct
     */
    private function buildMigrationStruct($path, $processedMigrations): MigrationStruct
    {
        $migration = \pathinfo($path, \PATHINFO_FILENAME);
        $version = \basename(\dirname($path));
        $startDate = null;
        $finishDate = null;

        foreach ($processedMigrations as $processedMigration) {
            if ($processedMigration['version'] === $version && $processedMigration['migration'] === $migration) {
                $startDate = new \DateTime($processedMigration['start_date']);
                $finishDate = new \DateTime($processedMigration['finish_date']);
                break;
            }
        }

        return new MigrationStruct(
            $path,
            $startDate,
            $finishDate
        );
    }
}
