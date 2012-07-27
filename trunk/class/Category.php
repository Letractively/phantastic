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
 * Prend en charge les catégories.
 *
 * Les catégories sont supportées par un double fonctionnement :
 * - La structure des dossiers organisant les Posts donne les URL des catégories
 * - Le fichier de configuration comporte les traductions éventuelles.
 *
 * Avec cette classe, on a la liste des Posts pour chaque nœud.
 * 
 * @copyright 2012 Michel Petit
 * @author Michel Petit <petit.michel@gmail.com> 
 */
class Category
{
    protected static $arr_hier = array();

    protected $arr_name;
    protected $arr_node = array();
    protected $str_slug = null;
    protected $arr_ids = array();

    public function __construct($str_path)
    {

        $str_root = sprintf(
            '%s%s',
            Config::getInstance()->getDir()->src,
            Config::getInstance()->getDir()->post
        );


        $this->arr_node = explode(
            '/',
            preg_replace("@^$str_root@", '', $str_path)
        );

        if(count($this->arr_node))
        {
            $arr_cat = Config::getInstance()->getCategories();
            
            for($i = 0; $i < count($this->arr_node); $i++)
            {
                if(isset($arr_cat[$this->arr_node[$i]]))
                {
                    $this->arr_name[] = $arr_cat[$this->arr_node[$i]];
                }
                else
                {
                    $this->arr_name[] = $this->arr_node[$i];
                }
            }
        }

    }

    /**
     * Retourne les catégories, ou une d’entre elles si un argument est passé. 
     * 
     * @param string $key 
     * @static
     * @access public
     * @return mixed Soit un array soit un objet Category
     */
    public static function getHier($key = null)
    {
        if(is_null($key))
        {
            return self::$arr_hier;
        }
        else
        {
            return self::$arr_hier[$key];
        }
    }

    public static function set($str_path)
    {
        $cat = new self($str_path);
        $cat->addToHier();

        return self::$arr_hier[$cat->getSlug()];
    }


    /**
     * Ajoute la catégorie instanciée à la liste des autres catégories.
     *
     * Cette méthode stocke l’instanciation courante dans une liste statique de 
     * la classe Category. 
     * 
     * @access public
     * @return void
     */
    public function addToHier()
    {
        if(!isset(self::$arr_hier[$this->getSlug()]))
        {
            self::$arr_hier[$this->getSlug()] = $this;
        }
    }




    /**
     * Ajoute l’ID d’un Post à la liste de l’objet Category. 
     * 
     * @param integer $int_id 
     * @access public
     * @return void
     */
    public function addId($int_id)
    {
        if($int_id > 0)
        {
            $this->arr_ids[] = $int_id;
        }
    }


    /**
     * Donne le nombre d’ID Post stockés pour l’objet Category.
     *
     * @return integer
     */
    public function getCount()
    {
        return count($this->arr_ids);
    }

    /**
     * Retourne le nom de la catégorie.
     *
     * @return string
     */
    public function getName()
    {
        return $this->arr_name[count($this->arr_name) - 1];
    }

    /**
     * Retourne l’arborescence de la catégorie actuelle. 
     * 
     * Donne un tableau retournant l’arborescence de la catégorie, avec en 
     * premier les parents et à la fin les enfants.
     *
     * @access public
     * @return array
     */
    public function getNode()
    {
        return $this->arr_node;
    }

    /**
     * ID des fichiers Post de cette catégorie.
     * 
     * @access public
     * @return array
     */
    public function getFileIds()
    {
        return $this->arr_ids;
    }

    /**
     * Retourne le slug propre à cette catégorie 
     * 
     * @access public
     * @return string
     */
    public function getSlug()
    {
        if(is_null($this->str_slug))
        {
            $this->str_slug = implode('/', $this->arr_node);
        }

        return $this->str_slug;
    }

}
