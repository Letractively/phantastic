<?php
/*
 * This file is part of Phantastic.
 *
 * Phantastic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Phantastic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Phantastic.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Malenki\Phantastic;

class Server
{
    const HOST = 'localhost:8080';
    const EXEC_PHP = 'php -S %s -t %s';
    const EXEC_PYTHON = 'cd %s && python -m SimpleHTTPServer %s; cd -';

    protected $str_host = null;

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
        if(self::hasInternalServer())
        {
            system(sprintf(self::EXEC_PHP, $this->str_host, Config::getInstance()->getDir()->dest));
        }
        else
        {
            $str_port = array_pop(explode(':', $this->str_host));
            system(sprintf(self::EXEC_PYTHON, Config::getInstance()->getDir()->dest, $str_port));
        }
    }
}
