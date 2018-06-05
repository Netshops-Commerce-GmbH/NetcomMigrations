<?php

namespace NetcomMigrations\Components\Migrations;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Migration
 */
class Migration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var SymfonyStyle */
    protected $io;

    /**
     * Constructor
     *
     * @param SymfonyStyle $io
     */
    final public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * Get the version of this migration.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return \basename(__DIR__);
    }

    /**
     * Get the name of the class.
     *
     * @return string
     */
    public function getName(): string
    {
        return self::class;
    }

    /**
     * Get SymfonyStyle input/output service.
     *
     * @return SymfonyStyle
     */
    public function getIO(): SymfonyStyle
    {
        return $this->io;
    }

    /**
     * Initialize the migration. This method will be called before `up` and `down`.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Do the migration
     *
     * @return void
     */
    public function up()
    {
    }

    /**
     * Undo the migration
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get the container
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
