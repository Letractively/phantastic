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

    //TODO: À améliorer, il manque pleins de caractères des langues européennes.
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


    public static function findCategoryFor(File $file)
    {
        $key = preg_replace('@'.Path::getSrcPost().'@', '',$file->getObjPath()->getPath());
        return Category::getHier($key);
    }

}
