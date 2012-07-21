<?php

namespace Malenki\Phantastic;

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

    public static function getHier()
    {
        return self::$arr_hier;
    }

    public static function set($str_path)
    {
        $cat = new self($str_path);
        $cat->addToHier();

        return self::$arr_hier[$cat->getSlug()];
    }


    public function addToHier()
    {
        if(!isset(self::$arr_hier[$this->getSlug()]))
        {
            self::$arr_hier[$this->getSlug()] = $this;
        }
    }




    public function addId($int_id)
    {
        if($int_id > 0)
        {
            $this->arr_ids[] = $int_id;
        }
    }


    /**
     * @return integer
     */
    public function getCount()
    {
        return count($this->arr_ids);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->arr_name[count($this->arr_name) - 1];
    }

    public function getNode()
    {
        return $this->arr_node;
    }

    /**
     * ID des fichiers
     * 
     * @access public
     * @return array
     */
    public function getFileIds()
    {
        return $this->arr_ids;
    }

    public function getSlug()
    {
        return implode('/', $this->arr_node);
    }

}
