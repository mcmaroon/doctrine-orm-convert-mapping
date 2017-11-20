<?php
namespace DoctrineOrmConvertMapping\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\InputInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
// ~
use DoctrineOrmConvertMapping\Helper;

class SchemaCommand extends Helper\Command
{

    private $destPath;
    private $outPath;

    protected function configure()
    {
        $this
            ->setName('app:schema')
            ->setDescription('Create, update, validate and revision schema sql.')
            ->setDefinition(array(
                new InputArgument(
                    'dbname', InputArgument::REQUIRED, 'required database name'
                ),
                new InputArgument(
                    'dbuser', InputArgument::OPTIONAL, '', 'root'
                ),
                new InputArgument(
                    'dbpass', InputArgument::OPTIONAL, '', ''
                ),
                new InputOption(
                    'dest-path', NULL, InputOption::VALUE_OPTIONAL, 'The path to your entities classes.', self::DEFAULT_DEST_PATH
                ),
                new InputOption(
                    'out-path', NULL, InputOption::VALUE_OPTIONAL, 'The path to generate your entities schema files.', self::DEFAULT_DEST_PATH
                ),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbParams = $this->getConnectionParams($input);
        $this->destPath = $input->getOption('dest-path');
        $this->outPath = $input->getOption('out-path');

        new Helper\Log($this->getName() . ':dbParams', $dbParams);

        $this->checkDestPath($this->destPath);
        $this->createDestPath($this->outPath);

        $output->writeln("Schema destination directory - " . $this->destPath);
        $output->writeln("Schema output directory - " . $this->outPath);

        $config = $this->getAnnotationMetadataConfiguration($this->destPath);
        $em = $this->getEntityManager($dbParams, $config);
        try {
            $em->beginTransaction();
        } catch (\Exception $exc) {
            throw new \InvalidArgumentException($exc->getMessage());
        }

        $this->getHelperSet()->set(new EntityManagerHelper($em), 'em');

        // ~

        $this->executeOrmCommand(self::SCHEMA_TYPE_CREATE, new ArrayInput([
            'command' => 'orm:schema-tool:create',
            '--dump-sql' => true,
            ]), $output);

        $this->executeOrmCommand(self::SCHEMA_TYPE_UPDATE, new ArrayInput([
            'command' => 'orm:schema-tool:update',
            '--dump-sql' => true,
            '--force' => true
            ]), $output);

        $this->executeOrmCommand(self::SCHEMA_TYPE_DROP, new ArrayInput([
            'command' => 'orm:schema-tool:drop',
            '--dump-sql' => true
            ]), $output);

        $validateCommand = $this->getApplication()->find('orm:validate-schema');
        $validateCommandInput = new ArrayInput([
            'command' => 'orm:validate-schema',
        ]);
        $validateCommand->run($validateCommandInput, $output);
    }

    protected function executeOrmCommand($commandType, InputInterface $input, OutputInterface $output)
    {
        $revisionCommandTypes = [
            self::SCHEMA_TYPE_UPDATE
        ];

        $schemaPath = $this->outPath . self::DEFAULT_SCHEMA_FILE_NAME . '-' . $commandType;
        $schemaFilePath = $schemaPath . self::DEFAULT_SCHEMA_FILE_EXT;
        if (\in_array($commandType, $revisionCommandTypes)) {
            $schemaFilePath = $schemaPath . '-' . date('Y-m-d_H-i-s') . self::DEFAULT_SCHEMA_FILE_EXT;
        }
        $streamHandle = \fopen($schemaFilePath, 'w+');
        $streamOutput = new StreamOutput($streamHandle);
        $createCommand = $this->getApplication()->find($input->getParameterOption('command'));
        $createCommand->run($input, $streamOutput);
        \fclose($streamHandle);

        // ~

        $fileContext = \file_get_contents($schemaFilePath);
        $noResultsStrings = [
            'Nothing to update',
            'No Metadata Classes',
        ];
        foreach ($noResultsStrings as $noResultsString) {
            if (\strpos($fileContext, $noResultsString) !== false) {
                \unlink($schemaFilePath);
                $output->writeln("No changes for command: " . $commandType);
                $output->writeln("Deletes a file: " . $schemaFilePath);
            }
        }
    }
}
