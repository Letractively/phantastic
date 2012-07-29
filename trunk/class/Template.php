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

class Template 
{
    protected $arr_data = array();
    protected $str_tmpl = null;

    public function __construct($tmpl)
    {
        $this->str_tmpl = $tmpl;
    }

    public function setContent($str)
    {
        $this->assign('content', $str);
    }

    public function assign($key, $value)
    {
        $this->arr_data[$key] = $value;
    }

    protected function partial($str)
    {
        $data = (object) $this->arr_data;
        require(
            sprintf(
                '%s%s.phtml',
                Config::getInstance()->getDir()->template,
                $str
            )
        );
    }

    public function render()
    {
        $data = (object) $this->arr_data;

        ob_start();
        require(
            sprintf(
                '%s%s.phtml',
                Config::getInstance()->getDir()->template,
                $this->str_tmpl
            )
        );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
