<?php

namespace NetcomMigrations\Command;

use Doctrine\Common\Collections\ArrayCollection;
use NetcomMigrations\Components\StubGenerator;
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
    /** @var string $commandName */
    private $commandName;
    /** @var string $stubsDir */
    private $stubsDir;
    /** @var string $pluginName */
    private $pluginName;
    /** @var ArrayCollection $migrationDirs */
    private $migrationDirs;
    /** @var StubGenerator $stubGenerator */
    private $stubGenerator;

    /**
     * CreateCommand constructor.
     *
     * @param string          $commandName
     * @param string          $stubsDir
     * @param string          $pluginName
     * @param ArrayCollection $migrationDirs
     * @param StubGenerator   $stubGenerator
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \LogicException
     */
    public function __construct(
        string $commandName,
        string $stubsDir,
        string $pluginName,
        ArrayCollection $migrationDirs,
        StubGenerator $stubGenerator
    ) {
        $this->commandName = $commandName;
        $this->stubsDir = $stubsDir;
        $this->pluginName = $pluginName;
        $this->migrationDirs = $migrationDirs;
        $this->stubGenerator = $stubGenerator;

        parent::__construct($this->commandName);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName($this->commandName)
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
            )
            ->addArgument(
                'plugin',
                InputArgument::OPTIONAL,
                'Name your migration by what it does (e.g. ImportArticleAttributes).',
                $this->pluginName
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $version = $input->getArgument('version');
        $plugin = $input->getArgument('plugin');
        $io = new SymfonyStyle($input, $output);

        $migrationDir = $this->getMigrationDirByPlugin($plugin);

        if (empty($migrationDir)) {
            throw new \InvalidArgumentException(
                \sprintf('Could not find a migrations directory for plugin "%s".', $plugin)
            );
        }

        try {
            $path = $this->stubGenerator->generate(
                $this->stubsDir . '/MigrationClass.stub',
                $migrationDir . '/' . $version . '/' . \date('YmdHis') . '_' . $name . '.php',
                [
                    ':CLASS:' => \ucfirst($name) . \date('YmdHis'),
                ]
            );

            $io->success(\sprintf('Generated new migration file %s', $path));
        } catch (\RuntimeException $exception) {
            $output->writeln('ERROR: ' . $exception->getMessage());
        }
    }

    /**
     * @param string $plugin
     *
     * @return string
     */
    private function getMigrationDirByPlugin(string $plugin) : string
    {
        foreach ($this->migrationDirs as $migrationDir) {
            if (\array_keys($migrationDir)[0] === $plugin) {
                return \reset($migrationDir);
            }
        }

        return '';
    }
}
