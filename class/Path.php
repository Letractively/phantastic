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
 */
class Path
{
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

    public static function createSlug($str)
    {
        // to lower case
        $str = mb_strtolower($str, 'UTF-8');

        // Remove diacritics
        $arr_prov = array(
            "é" => "e", "è" => "e", "ê" => "e", "ë" => "e", "ę" => "e", "ẽ" => 
            "e", 'ě' => 'e', "á" => "a", "à" => "a", "â" => "a", "ä" => "a", 
            "ą" => "a", "ã" => "a", "å" => "a", 'ǎ' => 'a', 'ã' => 'a', "ó" => 
            "o", "ò" => "o", "ô" => "o", "ö" => "o", "õ" => "o", 'ǒ' => 'o', 
            'ø' => 'o', 'õ' => 'o', "í" => "i", "ì" => "i", "î" => "i", "ï" => 
            "i", "ĩ" => "i", 'ǐ' => 'i', "ú" => "u", "ù" => "u", "û" => "u", 
            "ü" => "u", "ũ" => "u", "ů" => "u", 'ǔ' => 'u', "ý" => "y", "ỳ" => 
            "y", "ŷ" => "y", "ÿ" => "y", "ỹ" => "y", "ç" => "c", "ñ" => "n", 
            'ł' => 'l', 'ð' => 'dh', 'þ' => 'th', "œ" => "oe", "æ" => "ae", "ß" 
            => "ss", 'ŀl' => 'll',
                 
        );
       
        foreach($arr_prov as $k => $v)
        {
            $str = preg_replace(sprintf('/%s/', $k), $v, $str);
        }

        $str = preg_replace('/[^a-z]+/', '-', trim($str));
        $str = trim($str, '-');

        return $str;	
    }


    //TODO: todo :)
    public static function url($obj)
    {
        return null;
    }

    public static function build($obj)
    {
        //TODO à améliorer, ce n’est qu’un premier jet…
        $str_out = '';

        if($obj instanceof Tag)
        {
            $str_out = self::getDest() . $obj->getSlug() . '/index.html';

        }
        elseif($obj instanceof File)
        {
            $str_slug = '';

            if($obj->isPost())
            {
                $str_out = sprintf(File::PERMALINK_POST, Path::findCategoryFor($obj)->getSlug(), $obj->getTitleSlug()) . 'index.html';
            }
            else if($obj->isPage())
            {
                if(isset($obj->getHeader()->permalink))
                {
                    $str_slug = $obj->getHeader()->permalink;
                }
                else
                {
                    $str_slug = Path::createSlug($obj->getHeader()->title);
                }
                
                if(preg_match('@/$@', $str_slug))
                {
                    $str_out = $str_slug . 'index.html';
                }
                else
                {
                    $str_out = $str_slug;
                }
            }
            else
            {
                $str_out = preg_replace(
                    '@' . self::getSrc() . '@',
                    '',
                    $obj->getSrcPath()
                );
            }

            $str_out = self::getDest() . $str_out;
            
        }

        if(!file_exists(dirname($str_out)))
        {
            mkdir(dirname($str_out), 0755, true);
        }

        return $str_out;
    }


    public static function findCategoryFor(File $file)
    {
        $key = preg_replace('@'.Path::getSrcPost().'@', '',$file->getObjPath()->getPath());
        return Category::getHier($key);
    }

}
