<?php

namespace NetcomMigrations\Components\Structs;

/**
 * Class MigrationStruct
 */
class MigrationStruct
{
    /** @var string $path */
    private $path;
    /** @var string $pluginName */
    private $pluginName;
    /** @var \DateTime|null $startDate */
    private $startDate;
    /** @var \DateTime|null $finishDate */
    private $finishDate;

    /**
     * MigrationStruct constructor.
     *
     * @param string         $path
     * @param string         $pluginName
     * @param \DateTime|null $startDate
     * @param \DateTime|null $finishDate
     */
    public function __construct(
        string $path,
        string $pluginName,
        $startDate,
        $finishDate
    ) {
        $this->path = $path;
        $this->pluginName = $pluginName;
        $this->startDate = $startDate;
        $this->finishDate = $finishDate;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return \pathinfo($this->path, \PATHINFO_FILENAME);
    }

    /**
     * @return string
     */
    public function getMigration(): string
    {
        return \basename($this->getFileName(), '.php');
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return \basename(\dirname($this->path));
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->startDate !== null && $this->finishDate !== null;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return !$this->isFinished();
    }
}
