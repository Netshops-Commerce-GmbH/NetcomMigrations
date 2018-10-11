<?php

namespace NetcomMigrations\Components\Migrations;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class MigrationDirsCollectorFactory
 */
class MigrationDirsCollector
{
    /** @var \Enlight_Event_EventManager */
    protected $eventManager;

    /**
     * MigrationDirsCollectorFactory constructor.
     *
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(\Enlight_Event_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return ArrayCollection
     *
     * @throws \Enlight_Event_Exception
     */
    public function getMigrationDirs(): ArrayCollection
    {
        $collection = new ArrayCollection();

        $this->eventManager->collect('NetcomMigrations_Collect_Migrations', $collection);

        return $collection;
    }
}
