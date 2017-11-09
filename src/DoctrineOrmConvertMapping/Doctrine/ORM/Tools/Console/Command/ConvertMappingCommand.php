<?php

namespace DoctrineOrmConvertMapping\Doctrine\ORM\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
// ~
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
// ~
use DoctrineOrmConvertMapping\Doctrine\DBAL\Platforms\MySqlPlatform;
use DoctrineOrmConvertMapping\Doctrine\DBAL\Schema\MySqlSchemaManager;
use DoctrineOrmConvertMapping\Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use DoctrineOrmConvertMapping\Helper;

class ConvertMappingCommand extends Helper\Command {
    
    protected function configure() {
        $this
                ->setName('orm:convert-mapping')
                ->setAliases(array('orm:convert:mapping'))
                ->setDescription('Convert mapping information between supported formats.')
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

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dbParams = [
            'platform' => new MySqlPlatform(),
            'driver' => 'pdo_mysql',
            'dbname' => $input->getArgument('dbname'),
            'user' => $input->getArgument('dbuser'),
            'password' => $input->getArgument('dbpass')
        ];
        $params = [
            'dest-path' => $input->getOption('dest-path') . DIRECTORY_SEPARATOR . $dbParams['dbname']
        ];

        new Helper\Log($this->getName() . ':dbParams', \array_merge($dbParams, $params));

        // Process destination directory
        if (!is_dir($destPath = $params['dest-path'])) {
            mkdir($destPath, 0775, true);
        }
        $destPath = realpath($destPath);

        if (!file_exists($destPath)) {
            throw new \InvalidArgumentException(
            sprintf("Mapping destination directory '<info>%s</info>' does not exist.", $input->getArgument('dest-path'))
            );
        }

        if (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
            sprintf("Mapping destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        $output->writeln("Mapping destination directory - " . $destPath);

        $em = $this->getEntityManager($dbParams);
        $conn = $em->getConnection();
        $platform = $em->getConnection()->getDatabasePlatform();

        $em->getConfiguration()->setMetadataDriverImpl(
                new DatabaseDriver(new MySqlSchemaManager($conn, $platform))
        );

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadata = $cmf->getAllMetadata();

        $cme = new ClassMetadataExporter();
        $exporter = $cme->getExporter('php', $params['dest-path']);
        $exporter->setMetadata($metadata);
        $exporter->setOverwriteExistingFiles(true);
        $exporter->export();
    }

}
