<?php

use DoctrineOrmConvertMapping\Helper;

class RoboFile extends \Robo\Tasks
{

    /**
     * Clear cache
     */
    public function appClearCache()
    {
        $dirs = [
            Helper\Command::DEFAULT_DEST_PATH,
            Helper\Log::LOG_PATH
        ];
        $this->cleanDirectories($dirs);
    }

    /**
     * Clear cache Alias
     */
    public function appCC()
    {
        $this->appClearCache();
    }
    
    public function appSampleSchema()
    {
        $this->taskExec('php run.php app:schema doctrine-orm-convert-mapping --dest-path=sample/schema --table-prefix=prefix_')->run();
    }
    
    public function appSampleConvertMapping()
    {
        $this->taskExec('php run.php app:convert-mapping doctrine-orm-convert-mapping --to-type=annotation')->run();
    }

    protected function cleanDirectories(array $dirs)
    {
        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                try {
                    $this->_cleanDir($dir);
                } catch (\Exception $exc) {
                    $this->say($exc->getMessage());
                }
            }
        }
    }
}
