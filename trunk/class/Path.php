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


/**
 * Classe relative à la création d’URL ou de chemin dans un FS. 
 * 
 * @package Pahntastic
 * @copyright 2012 Michel Petit
 * @author Michel Petit <petit.michel@gmail.com> 
 * @todo Il faudra prévoir un moyen pour manipuler les chemins sous windows…
 */
class Path
{
    const SRC = 'src/';
    const DEST = 'out/';
    const POST = 'post/';
    const TEMPLATE = 'template/';
    const TAGS = 'tags/';


    public static function cleanPath($str_path)
    {
        return preg_replace(
            sprintf('@%s+@', self::getDirectorySeparator()),
            self::getDirectorySeparator(),
            $str_path
        );
    }


    public static function createIndex($str_path)
    {
        if(!preg_match(sprintf('@%s[a-z\.]+$@', self::getDirectorySeparator()), $str_path))
        {
            $str_path = $str_path . self::getDirectorySeparator() . 'index.html';
        }

        return self::cleanPath($str_path);
    }

    /**
     * Retourne le séparateur de répertoire en fonction du système.
     *
     * Sur Microsoft Windows, retourne '\', sur UNIX retourne '/'. 
     * 
     * @static
     * @access public
     * @return string
     */
    public static function getDirectorySeparator()
    {
        return DIRECTORY_SEPARATOR;
    }

    public static function getSrcPost()
    {
        return sprintf(
            '%s%s',
            Config::getInstance()->getDir()->src,
            Config::getInstance()->getDir()->post
        );
    }

    public static function getSrc()
    {
        return Config::getInstance()->getDir()->src;
    }
    
    public static function getDest()
    {
        return Config::getInstance()->getDir()->dest;
    }
    
    public static function getTemplate()
    {
        return Config::getInstance()->getDir()->template;
    }


    /**
     * Construit l’URL de l’objet fourni. 
     * 
     * - Pour Tag, prend en référence le permalink défini dans la configuration
     * - Pour Page, soit la configuration globale est prise en compte, soit le 
     * permalink de l’en-tête YAML du fichier est pris en compte. Quoi qu’il en 
     * soit, c’est toujours le permalink de l’en-tête YAML qui prime sur la 
     * configuration globale.
     * - Pour les autres, leur URL est « calqué » sur leur chemin d’origine.
     *
     * @param mixed $obj 
     * @static
     * @access public
     * @return string
     *
     * @todo À coder dès que possible.
     */
    /*
    public static function url($obj)
    {
        if($obj instanceof Tag)
        {
            //Prendre ce qui est défini dans le fichier de configuration
            //Par exemple /tags/:title/
        }
        elseif($obj instanceof File)
        {
            if($obj->isPost())
            {
                //Prendre ce qui est défini dans le fichier de configuration 
                //SAUF si une directive « permalink » existe dans l’en-tête 
                //YAML du fichier
                //Par exemple /:year/:month/:day/:title.html
            }
            else if($obj->isPage())
            {
                //Prendre la directive « permalink » définie dans l’en-tête YAML
                //Par exemple /a-propos/
            }
            else
            {
                //Prendre le chemin tel que défini dans la source.
            }
        }
        return null;
    }
     */

    /**
     * Crée un chemin selon le type d’objet passé en argument. 
     * 
     * Selon que le type d’objet est un Tag, un File de type Post, un File de 
     * type Page ou un File de type autre, cette méthode crée le chemin, tant 
     * au niveau de la chaîne de caractères, qu’au niveau du système de fichier 
     * en créant le ou les dossiers nécessaires.
     *
     * @param mixed $obj un Objet Tag ou File 
     * @static
     * @access public
     * @return string
     *
     * @todo Peut-être que les objets de type Category pourront être utilisé.
     */
    public static function build($obj)
    {
        $str_out = self::cleanPath(self::getDest() . $obj->getUrl());

        if($obj instanceof Tag)
        {
            $str_out = self::createIndex($str_out);

        }
        elseif($obj instanceof File)
        {
            if(!$obj->isFile())
            {
                $str_out = self::createIndex($str_out);
            }
        }

        if(!file_exists(dirname($str_out)))
        {
            mkdir(dirname($str_out), 0755, true);
        }

        return $str_out;
    }


    public static function findCategoryFor(File $file)
    {
        if($file->getObjPath()->getPath(). Path::getDirectorySeparator() == Path::getSrcPost())
        {
            $key = Path::getDirectorySeparator();
        }
        else
        {
            $key = preg_replace('@'.Path::getSrcPost().'@', '',$file->getObjPath()->getPath());
        }
        return Category::getHier($key);
    }

}
