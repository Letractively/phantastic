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
use DateTimeZone;

/**
 * La configuration du programme.
 *
 * Créée par les options en ligne de commande ou par un fichier de 
 * configuration YAML, cette classe permet aussi l’utilisation de nombreux 
 * paramètres avec des valeurs par défaut.
 * 
 * @copyright 2012 Michel Petit
 * @author Michel Petit <petit.michel@gmail.com> 
 */
class Config
{

    protected static $mixed_yaml = null;

    protected static $obj_instance = null;

    protected $str_name = null;
    
    protected $str_meta = null;
    
    protected $str_timezone = 'UTC';
    
    protected $str_server = Server::HOST;

    protected $arr_categories = null;
    
    protected $str_base = null;
    
    protected $str_permalink_tag = null;

    protected $str_permalink_post = null;
    
    protected $obj_dir = null;



    protected static function basicCheck($str)
    {
        return preg_match('@/$@', $str);
    }



    /**
     * Permet de spécifier un fichier alternatif
     */
    public static function getInstanceWithConfigFile($str)
    {
        if(is_readable($str))
        {
            $mixed_yaml = Parser::parseYaml($str, true);

            if($mixed_yaml !== false)
            {
                self::$mixed_yaml = (object) $mixed_yaml;
                return self::getInstance();
            }
            else
            {
                throw new Exception(
                    sprintf('File %s is not a valid YAML setting file!', $str)
                );
            }
        }
        else
        {
            throw new Exception(
                sprintf('File %s does not exist or is not readable.', $str)
            );
        }
    }



    private function __construct()
    {
        $this->obj_dir = (object) array(
            'template' => Path::TEMPLATE,
            'post'     => Path::POST,
            'src'      => Path::SRC,
            'dest'     => Path::DEST
        );

        $this->str_base = Permalink::BASE;
        $this->str_permalink_tag = Permalink::TAG;
        $this->str_permalink_post = Permalink::POST;

        if(!is_null(self::$mixed_yaml))
        {
            $this->setName(self::$mixed_yaml->name);
            $this->setMeta(self::$mixed_yaml->meta);

            if(isset(self::$mixed_yaml->timezone))
                $this->setTimezone(self::$mixed_yaml->timezone);

            // si server actif, désactive l’URL de base pour avoir une 
            // navigation fonctionnnelle
            if(isset(self::$mixed_yaml->server))
            {
                $this->setServer(self::$mixed_yaml->server);
            }
            elseif(isset(self::$mixed_yaml->base))
                $this->setBase(self::$mixed_yaml->base);

            $this->setCategories(self::$mixed_yaml->categories);

            if(isset(self::$mixed_yaml->permalink['tag']))
                $this->setPermalinkTag(self::$mixed_yaml->permalink['tag']);
            
            if(isset(self::$mixed_yaml->permalink['post']))
                $this->setPermalinkPost(self::$mixed_yaml->permalink['post']);

            $this->setPostDir(self::$mixed_yaml->dir['post']);
            $this->setSrcDir(self::$mixed_yaml->dir['src']);
            $this->setDestDir(self::$mixed_yaml->dir['dest']);
            $this->setTemplateDir(self::$mixed_yaml->dir['template']);
            
        }
    }



    public static function getInstance()
    {
        if(is_null(self::$obj_instance))
        {
            self::$obj_instance = new self();
        }

        return self::$obj_instance;
    }



    public function setName($str)
    {
        $this->str_name = $str;
    }



    public function setMeta($str)
    {
        $this->str_meta = $str;
    }
    
    public function setServer($mixed)
    {
        if(is_bool($mixed))
        {
            if(!$mixed)
            {
                $this->str_server = $mixed;
            }
        }
        else
        {
            $this->str_server = $mixed;
        }
    }

    /**
     * setTimezone 
     * 
     * @param string $str 
     * @access public
     * @return string
     */
    public function setTimezone($str)
    {
        if(in_array($str, DateTimezone::listIdentifiers()))
        {
            $this->str_timezone = $str;
        }
        else
        {
            throw new Exception('Timezone définie incorrecte.');
        }
    }

    public function setCategories($arr)
    {
        $this->arr_categories = $arr;
    }



    public function setBase($str)
    {
        if(self::basicCheck($str))
        {
            $this->str_base = $str;
        }
        else
        {
            throw new Exception('Base URL must ending with a slash.');
        }
    }

    public function setPermalinkTag($str)
    {
        $this->str_permalink_tag = $str;
    }

    public function setPermalinkPost($str)
    {
        $this->str_permalink_post = $str;
    }

    public function setPostDir($str)
    {
        if(self::basicCheck($str))
        {
            $this->obj_dir->post = $str;
        }
        else
        {
            throw new Exception('Custom posts’ directory must have a slash at the end.');
        }
    }
    
    public function setSrcDir($str)
    {
        if(self::basicCheck($str))
        {
            $this->obj_dir->src = $str;
        }
        else
        {
            throw new Exception('Custom source directory must have a slash at the end.');
        }
    }

    public function setDestDir($str)
    {
        if(self::basicCheck($str))
        {
            $this->obj_dir->dest = $str;
        }
        else
        {
            throw new Exception('Custom destination directory must have a slash at the end.');
        }
    }

    public function setTemplateDir($str)
    {
        if(self::basicCheck($str))
        {
            $this->obj_dir->template = $str;
        }
        else
        {
            throw new Exception('Custom destination directory must have a slash at the end.');
        }
    }

    public function getName()
    {
        return $this->str_name;
    }

    public function getMeta()
    {
        return $this->str_meta;
    }

    public function getTimezone()
    {
        return $this->str_timezone;
    }

    public function getServer()
    {
        return $this->str_server;
    }

    public function getCategories()
    {
        return $this->arr_categories;
    }

    public function getBase()
    {
        return $this->str_base;
    }

    public function getPermalinkTag()
    {
        return $this->str_permalink_tag;
    }
    
    public function getPermalinkPost()
    {
        return $this->str_permalink_post;
    }

    //TODO: Créer des raccourcis du genre getDirPost()
    public function getDir()
    {
        return $this->obj_dir;
    }
}
