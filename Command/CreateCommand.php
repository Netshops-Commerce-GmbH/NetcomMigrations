<?php

namespace NetcomMigrations\Command;

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
    /** @var string $migrationsDir */
    private $migrationsDir;
    /** @var StubGenerator $stubGenerator */
    private $stubGenerator;

    /**
     * CreateCommand constructor.
     *
     * @param string        $commandName
     * @param string        $stubsDir
     * @param string        $migrationsDir
     * @param StubGenerator $stubGenerator
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(
        string $commandName,
        string $stubsDir,
        string $migrationsDir,
        StubGenerator $stubGenerator
    ) {
        $this->commandName = $commandName;
        $this->stubsDir = $stubsDir;
        $this->migrationsDir = $migrationsDir;
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
        $name = $input->getArgument('name');
        $version = $input->getArgument('version');
        $io = new SymfonyStyle($input, $output);

        try {
            $path = $this->stubGenerator->generate(
                $this->stubsDir . '/MigrationClass.stub',
                $this->migrationsDir . '/' . $version . '/' . \date('YmdHis') . '_' . $name . '.php',
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
