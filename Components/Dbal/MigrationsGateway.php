<?php

namespace NetcomMigrations\Components\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use NetcomMigrations\Components\Structs\MigrationStruct;

/**
 * Class MigrationsGateway
 */
class MigrationsGateway
{
    const TABLE_NETCOM_MIGRATIONS = 's_plugin_netcom_migrations';

    /** @var Connection $connection */
    private $connection;

    /**
     * MigrationsGateway constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function getProcessedMigrations(): array
    {
        $query = $this->connection->createQueryBuilder();

        return $query->select('*')
            ->from(self::TABLE_NETCOM_MIGRATIONS)
            ->orderBy('start_date', 'ASC')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param MigrationStruct $migration
     *
     * @return string
     */
    public function insert(MigrationStruct $migration)
    {
        $this->connection->insert(
            self::TABLE_NETCOM_MIGRATIONS,
            [
                'version'    => $migration->getVersion(),
                'migration'  => $migration->getMigration(),
                'start_date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]
        );

        return $this->connection->lastInsertId();
    }

    /**
     * @param int $insertId
     */
    public function markFinished($insertId)
    {
        $this->connection->update(
            self::TABLE_NETCOM_MIGRATIONS,
            [
                'finish_date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $insertId,
            ]
        );
    }

    /**
     * @param string $version
     * @param string $migration
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public function delete($version, $migration): int
    {
        return $this->connection->delete(
            self::TABLE_NETCOM_MIGRATIONS,
            [
                'version'   => $version,
                'migration' => $migration,
            ]
        );
    }
}
