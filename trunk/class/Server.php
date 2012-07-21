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
