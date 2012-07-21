<?php

namespace Malenki\Phantastic;

class Server
{
    const EXEC_PHP = 'php -S %s -t %s';
    const EXEC_PYTHON = 'python -m SimpleHTTPServer %s';

    public static function hasInternalServer()
    {
        return phpversion() >= '5.4.0';
    }

    public function setHost($str)
    {
        $this->str_host = $str;
    }

    public function run()
    {
        //TODO: S’occuper de l’alternative Python : comment spécifier le répertoire ?
        if(self::hasInternalServer())
        {
            system(sprintf(self::EXEC_PHP, $this->str_host, Config::getInstance()->getDir()->dest));
        }
    }
}
