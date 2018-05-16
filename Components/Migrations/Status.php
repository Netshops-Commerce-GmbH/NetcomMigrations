<?php

namespace NetcomMigrations\Components\Migrations;

use Doctrine\Common\Collections\ArrayCollection;
use NetcomMigrations\Components\Dbal\MigrationsGateway;
use NetcomMigrations\Components\Structs\MigrationStruct;

/**
 * Class Status
 */
class Status
{
    /** @var MigrationsGateway $migrationsGateway */
    private $migrationsGateway;

    /** @var ArrayCollection $migrationDirs */
    private $migrationDirs;

    /**
     * Status constructor.
     *
     * @param MigrationsGateway $migrationsGateway
     * @param ArrayCollection   $migrationsDirs
     */
    public function __construct(MigrationsGateway $migrationsGateway, ArrayCollection $migrationsDirs)
    {
        $this->migrationsGateway = $migrationsGateway;
        $this->migrationDirs = $migrationsDirs;
    }

    /**
     * @param string $version
     *
     * @return MigrationStruct[]
     */
    public function getPendingMigrations($version = '') : array
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
    public function getFinishedMigrations() : array
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
    public function getAllMigrations() : array
    {
        $paths = [];

        foreach ($this->migrationDirs as $migrationConfig) {
            $pluginName = \array_keys($migrationConfig)[0];
            $migrationPaths = \glob(\reset($migrationConfig) . '/**/*.php');

            if (\count($migrationPaths)) {
                $paths[$pluginName] = $migrationPaths;
            }
        }

        if (!\count($paths)) {
            return [];
        }

        $migrationStructs = $this->buildMigrationStructs($paths);

        return $this->sortMigrationStructs($migrationStructs);
    }

    /**
     * @param array $paths
     *
     * @return MigrationStruct[]
     */
    private function buildMigrationStructs(array $paths) : array
    {
        /** @var MigrationStruct[] $processedMigrations */
        $processedMigrations = $this->migrationsGateway->getProcessedMigrations();
        $migrationStructs = [];

        foreach ($paths as $pluginName => $pluginPaths) {
            /** @var array $pluginPaths */
            foreach ($pluginPaths as $pluginPath) {
                $migrationStructs[] = $this->buildMigrationStruct($pluginPath, $pluginName, $processedMigrations);
            }
        }

        return $migrationStructs;
    }

    /**
     * @param string $path
     * @param string $pluginName
     * @param array  $processedMigrations
     *
     * @return MigrationStruct
     */
    private function buildMigrationStruct($path, $pluginName, $processedMigrations) : MigrationStruct
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
            $pluginName,
            $startDate,
            $finishDate
        );
    }

    /**
     * @param MigrationStruct[] $migrationStructs
     *
     * @return MigrationStruct[]
     */
    private function sortMigrationStructs(array $migrationStructs) : array
    {
        \usort(
            $migrationStructs,
            function ($a, $b) {
                /** @var MigrationStruct $a */
                /** @var MigrationStruct $b */

                return \version_compare($a->getVersion(), $b->getVersion());
            }
        );

        return $migrationStructs;
    }
}
