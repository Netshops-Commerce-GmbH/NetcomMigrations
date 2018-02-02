<?php

namespace NetcomMigrations\Command;

use NetcomMigrations\NetcomMigrations;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateCommand
 */
class CreateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('netcom:migrations:create')
            ->setDescription('Creates a new migration class.')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Provide the migration version in which the new migration shall be created.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name your migration by what it does (e.g. ImportArticleAttributes).'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stubsDir = $this->container->getParameter(NetcomMigrations::CONTAINER_PREFIX . '.stubs_dir');
        $migrationsDir = $this->container->getParameter(NetcomMigrations::CONTAINER_PREFIX . '.migrations_dir');
        $name = $input->getArgument('name');
        $version = $input->getArgument('version');
        $io = new SymfonyStyle($input, $output);

        try {
            $path = $this->container->get('netcom_migrations.components.stub_generator')->generate(
                $stubsDir . '/MigrationClass.stub',
                $migrationsDir . '/' . $version . '/' . \date('YmdHis') . '_' . $name . '.php',
                [
                    ':CLASS:' => \ucfirst($name) . \date('YmdHis'),
                ]
            );

            $io->success(\sprintf('Generated new migration file %s', $path));
        } catch (\RuntimeException $exception) {
            $output->writeln('ERROR: ' . $exception->getMessage());
        }
    }
}
