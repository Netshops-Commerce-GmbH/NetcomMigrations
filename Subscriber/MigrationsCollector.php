<?php

namespace NetcomMigrations\Subscriber;

/**
 * Class MigrationsCollector
 */
class MigrationsCollector
{
    /** @var string $pluginName */
    private $pluginName;

    /** @var string $migrationsDir */
    private $migrationsDir;

    /**
     * MigrationsCollector constructor.
     *
     * @param string $pluginName
     * @param string $migrationsDir
     */
    public function __construct(string $pluginName, string $migrationsDir)
    {
        $this->pluginName = $pluginName;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * @return array
     */
    public function onCollectMigrations(): array
    {
        return [$this->pluginName => $this->migrationsDir];
    }
}