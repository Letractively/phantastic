<?php

namespace Malenki\Phantastic;

class History
{
    const LAST_HOME = 3;

    protected static $arr_hist = array();

    protected $str_date = null;
    protected $arr_ids = array();

    public function __construct($str_date)
    {
        $this->str_date = $str_date;
    }

    public static function getHist()
    {
        return self::$arr_hist;
    }

    public static function set($str_date)
    {
        $hist = new self($str_date);
        $hist->addToHist();

        return self::$arr_hist[$str_date];
    }


    public function addToHist()
    {
        if(!isset(self::$arr_hist[$this->str_date]))
        {
            self::$arr_hist[$this->str_date] = $this;
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
     * ID des fichiers
     * 
     * @access public
     * @return array
     */
    public function getFileIds()
    {
        return $this->arr_ids;
    }

    /**
     * getLast 
     * 
     * @param integer $n 
     * @static
     * @access public
     * @return array
     */
    public static function getLast($n = self::LAST_HOME)
    {
        $arrProv = array();

        krsort(self::$arr_hist);

        foreach(self::$arr_hist as $h)
        {
            foreach($h->getFileIds() as $id)
            {
                if(count($arrProv) < $n)
                {
                    $arrProv[] = $id;
                }
                else
                {
                    break;
                }
            }
        }

        return $arrProv;
    }

    //TODO: à faire quand je déciderai de distribuer le code, pas urgent 
    //pour mon cas.
    public static function getFor($date){}

}
