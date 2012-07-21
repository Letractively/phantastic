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

use Malenki\Phantastic\Parser as Parser;

class File
{
    const PERMALINK_POST = '%s/%s/';
    const PERMALINK_TAG = 'tags/%s/';

    protected static $last_id = 0;
    protected $id = null;
    protected $obj_path = null;
    protected $str_url = null;
    protected $obj_head = null;
    protected $str_content = null;

    protected function read()
    {
        $p = new Parser($this->obj_path->getRealPath());
        if($p->hasHeader())
        {
            $this->obj_head = (object) $p->getHeader();

            if($p->hasContent())
            {
                $this->str_content = $p->getContent();
            }
        }

    }


    //TODO: À améliorer
    public static function createSlug($str)
    {
        // to lower case
        $str = mb_strtolower($str, 'UTF-8');

        // Remove diacritics
        $arr_prov = array(
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'ý' => 'y',
            'ỳ' => 'y',
            'ÿ' => 'y',
            'ŷ' => 'y',

            'ç' => 'c',
            'œ' => 'oe'
        );
        
        foreach($arr_prov as $k => $v)
        {
            $str = preg_replace(sprintf('/%s/', $k), $v, $str);
        }

        // Remove spaces and other stuffs
        $str = preg_replace('/[^a-z]+/', '-', trim($str));
        $str = trim($str, '-');

        return $str;	
    }

    public function __construct($path)
    {
        $this->obj_path = $path;
        self::$last_id++;
        $this->id = self::$last_id;
        $this->read();
    }

    public function hasHeader()
    {
        return is_object($this->obj_head);
    }

    public function getHeader()
    {
        return $this->obj_head;
    }

    public function getContent()
    {
        return $this->str_content;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPost()
    {
        return $this->hasHeader() && preg_match(
            '@'. Config::getInstance()->getDir()->src . Config::getInstance()->getDir()->post . '@',
            $this->obj_path->getPath()
        );
    }

    public function isPage()
    {
        return ($this->hasHeader() && !$this->isPost());
    }

    public function isFile()
    {
        return(!$this->isPost() && !$this->isPage());
    }

    public function getTitleSlug()
    {
        return self::createSlug($this->getHeader()->title);
    }

    public function getCitySlug()
    {
        return self::createSlug($this->getHeader()->city);
    }


    public function getUrl()
    {
        if($this->isPost())
        {
            //TODO: Mettre à jour pour les catégories
            return sprintf(self::PERMALINK_POST, $this->getCitySlug(), $this->getTitleSlug());
        }
        else if($this->isPage())
        {
            if(isset($this->getHeader()->permalink))
            {
                return $this->getHeader()->permalink;
            }
            else
            {
                return self::createSlug($this->getHeader()->title);
            }
        }
        else
        {
            //return preg_replace(
                //'@' . Config::getInstance()->getDir()->src . '@',
                //Config::getInstance()->getDir()->dest,
                //$this->obj_path->getPathName()
            //);
            return preg_replace(
                '@' . Config::getInstance()->getDir()->src . '@',
                '',
                $this->obj_path->getPathName()
            );
        }
    }

    public function getSrcPath()
    {
        return $this->obj_path->getPathName();
    }

    public function getDestPath()
    {
        $str_prov = $this->getUrl();

        if($this->isPost())
        {
            return $str_prov . 'index.html';
        }
        else if($this->isPage())
        {
            if(preg_match('@/$@', $str_prov))
            {
                return $str_prov . 'index.html';
            }
            else
            {
                return $str_prov;
            }
        }
        else
        {
            return $str_prov;
        }
    }
}
