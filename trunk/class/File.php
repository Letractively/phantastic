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
    const PERMALINK_POST = '%s/%s/'; //TODO: à dégager de là
    const PERMALINK_TAG = 'tags/%s/'; //TODO: à dégager de là

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

    /**
     * Détermine si le fichier est un fichier à interpréter ou non.
     *
     * Détermine si le fichier est un post ou une page. 
     * 
     * @access public
     * @return boolean
     */
    public function isFile()
    {
        return(!$this->isPost() && !$this->isPage());
    }

    public function getTitleSlug()
    {
        return Path::createSlug($this->getHeader()->title);
    }

    //TODO: Sera opérationnel en même temps que Path::url
    public function getUrl()
    {
        return Path::url($this);
    }


    public function getSrcPath()
    {
        return $this->obj_path->getPathName();
    }

    public function getObjPath()
    {
        return $this->obj_path;
    }
}
