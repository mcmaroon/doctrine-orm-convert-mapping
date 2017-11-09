<?php
namespace DoctrineOrmConvertMapping\Helper;

class Log
{

    const LOG_PATH = 'var/log/';
    const FILE_NAME = 'debug.log';

    public function __construct($name, array $data = array())
    {
        if (class_exists('Monolog\Logger')) {
            $log = new \Monolog\Logger('mpc');
            $log->pushHandler(new \Monolog\Handler\StreamHandler(self::LOG_PATH . self::FILE_NAME));
            $log->debug($name, $data);
        }
    }
}
