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

    /**
     * orm:convert-mapping
     */
    public function appConvertMapping($string)
    {
        $this->taskExec('php run.php orm:convert-mapping ' . $string)->run();
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
