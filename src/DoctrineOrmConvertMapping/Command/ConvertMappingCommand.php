<?php
namespace DoctrineOrmConvertMapping\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
// ~
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\EntityGenerator;
// ~
use DoctrineOrmConvertMapping\Doctrine\DBAL\Schema\MySqlSchemaManager;
use DoctrineOrmConvertMapping\Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use DoctrineOrmConvertMapping\Helper;

class ConvertMappingCommand extends Helper\Command
{

    const DEFAULT_MAPPING_TYPE = 'php';

    protected function configure()
    {
        $this
            ->setName('app:convert-mapping')
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
                new InputOption(
                    'to-type', NULL, InputOption::VALUE_OPTIONAL, 'The mapping type to be converted.', self::DEFAULT_MAPPING_TYPE
                ),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbParams = $this->getConnectionParams($input);
        $destPath = $input->getOption('dest-path');
        $toType = strtolower($input->getOption('to-type'));

        new Helper\Log($this->getName() . ':dbParams', $dbParams);

        $this->createDestPath($destPath);

        $output->writeln("Mapping destination directory - " . $destPath);
        $output->writeln("Mapping type " . $toType);

        $config = $this->getAnnotationMetadataConfiguration($destPath);
        $em = $this->getEntityManager($dbParams, $config);
        try {
            $em->beginTransaction();
        } catch (\Exception $exc) {
            throw new \InvalidArgumentException($exc->getMessage());
        }

        $conn = $em->getConnection();
        $platform = $em->getConnection()->getDatabasePlatform();

        $em->getConfiguration()->setMetadataDriverImpl(
            new DatabaseDriver(new MySqlSchemaManager($conn, $platform))
        );

        // ~

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadata = $cmf->getAllMetadata();
        if (!count($metadata)) {
            $output->writeln('No Metadata Classes to process.');
        }

        // ~

        $cme = new ClassMetadataExporter();
        $exporter = $cme->getExporter($toType, $destPath);
        $exporter->setOverwriteExistingFiles(true);

        if ($toType == 'annotation') {
            $entityGenerator = new EntityGenerator();
            $exporter->setEntityGenerator($entityGenerator);
        }

        $exporter->setMetadata($metadata);
        $exporter->export();
    }
}
