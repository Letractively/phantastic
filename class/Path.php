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

    /**
     * Crée des morceaux d’URL avec uniquement des caractères ASCII et des 
     * tirets d’hyphénation.
     *
     * Cette méthode statique prend en argument une chaîne de caractères 
     * qu’elle traite de manière à en convertir les caractères portant des 
     * diacritiques ou des caractères composés en caractères équivalents ASCII 
     * minuscules.
     *
     * Ainsi, par exemple, la chaîne suivante : « BŒUF » donnera « boeuf » et 
     * celle-ci : « théâtre » donnera « theatre ».
     *
     * Ensuite, tout ce qui n’est pas un caractère alphanumérique ASCII et tout 
     * ce qui n’est pas un tiret est éliminé, les tirets prennent la place de 
     * caractères spéciaux, comme les espaces, les ponctuations… Et les 
     * doublons tirets sont enlevés pour n’en laisser qu’un. La chaîne obtenue 
     * ne doit ni commencer, ni finir par un tiret.
     *
     * Ainsi, la phrase « Mais ?! Où est donc Ornicar ? » donnera 
     * « mais-ou-est-donc-ornicar ».
     *
     * Pour le moment, les langues d’Europe Occidentales sont supportées, mais 
     * bientôt quelques langues non basées sur un alphabet latin seront 
     * supportées. Ainsi le grec et le russe feront leur apparition avec un 
     * système de translitération. Soyez donc patient ;)
     * 
     * @param string $str 
     * @static
     * @access public
     * @return string
     * @todo Supporter d’autres langues à alphabet latin dérivé comme le Turc, 
     * des langues d’Europe de l’Est, le Serbo-Croate, le Polonais, le Roumain, 
     * etc.
     * @todo Supporter l’esperanto, en utilisant la notation « x ».
     * @todo Faire un premier support des langues n’utilisant pas un alphabet 
     * latin. S’occuper alors en priorité du grec, du russe, du bulgare, de 
     * l’ukrainien.
     * @todo En priorité basse, voir pour le Coréen (langue à syllabe), voir 
     * s’il est possible d’obtenir un truc sympa sans trop faire compliqué.
     */
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
     * @todo Il faut réécrire cette méthode suite aux évolutions récentes. 
     * Peut-être que les objets de type Category pourront être utilisé.
     */
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
