<?php

namespace NetcomMigrations;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class NetcomMigrations
 */
class NetcomMigrations extends Plugin
{
    /**
     * @param InstallContext $context
     *
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $this->executeInstallSql();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter($this->getContainerPrefix() . '.app_dir', $this->getShopwareBasePath());
        $container->setParameter($this->getContainerPrefix() . '.stubs_dir', $this->getPath() . '/Resources/Stubs');
        $container->setParameter($this->getContainerPrefix() . '.migrations_dir', $this->getPath() . '/Migrations');

        parent::build($container);
    }

    /**
     * Returns absolute path to Shopware root directory.
     *
     * @return string
     */
    public function getShopwareBasePath(): string
    {
        $path = $this->getPath();
        $parts = \explode('/', $path);

        \array_pop($parts);
        \array_pop($parts);
        \array_pop($parts);

        return \implode('/', $parts);
    }

    /**
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    protected function executeInstallSql()
    {
        $sqlPath = $this->getPath() . '/Resources/sql/install.sql';

        if (!\file_exists($sqlPath)) {
            throw new \RuntimeException(sprintf('Import file "%s" does not exists', $sqlPath));
        }

        $this->container->get('dbal_connection')->exec(
            \file_get_contents($sqlPath)
        );
    }
}
