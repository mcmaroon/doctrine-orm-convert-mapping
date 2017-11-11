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
                    'dest-path', NULL, InputOption::VALUE_OPTIONAL, 'The path to generate your entities classes.', self::DEFAULT_DEST_PATH
                ),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbParams = $this->getConnectionParams($input);
        $destPath = $input->getOption('dest-path');

        new Helper\Log($this->getName() . ':dbParams', $dbParams);

        $this->checkDestPath($destPath);

        $output->writeln("Schema destination directory - " . $destPath);

        $config = $this->getAnnotationMetadataConfiguration($destPath);
        $em = $this->getEntityManager($dbParams, $config);
        try {
            $em->beginTransaction();
        } catch (\Exception $exc) {
            throw new \InvalidArgumentException($exc->getMessage());
        }

        $this->getHelperSet()->set(new EntityManagerHelper($em), 'em');

        $sqlFilePath = $destPath . DIRECTORY_SEPARATOR . self::DEFAULT_SCHEMA_FILE_NAME;

        // ~

        $streamHandle = \fopen($sqlFilePath, 'w+');
        $streamOutput = new StreamOutput($streamHandle);
        $createCommand = $this->getApplication()->find('orm:schema-tool:create');
        $createCommandInput = new ArrayInput([
            'command' => 'orm:schema-tool:create',
            '--dump-sql' => true,
        ]);
        $createCommand->run($createCommandInput, $streamOutput);
        \fclose($streamHandle);

        // ~

        $updateCommand = $this->getApplication()->find('orm:schema-tool:update');
        $updateCommandInput = new ArrayInput([
            'command' => 'orm:schema-tool:update',
            '--force' => true,
            '--dump-sql' => true,
        ]);
        $updateCommand->run($updateCommandInput, $output);

        // ~

        $validateCommand = $this->getApplication()->find('orm:validate-schema');
        $validateCommandInput = new ArrayInput([
            'command' => 'orm:validate-schema',
        ]);
        $validateCommand->run($validateCommandInput, $output);
    }
}
