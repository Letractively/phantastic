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

class Permalink
{
    protected static $arr_placeholders = array(
        ':categories',
        ':year',
        ':month',
        ':day',
        ':title'
    );

    protected $str_permalink = null;
    protected $str_url = null;
    protected $int_count = 0;
    protected $arr_keys_values = array();


    /**
     * Détermine si la chaîne du placeholder passée en paramètre est correcte. 
     * 
     * Il y a un nombre limité de placeholders supportés. Cette méthode 
     * détermine si un placeholder donné existe bel et bien.
     *
     * @param string $str 
     * @static
     * @access protected
     * @return boolean
     */
    protected static function checkPlaceholderName($str)
    {
        return in_array($str, self::$arr_placeholders);
    }

    /**
     * Lit le permalink pour en déterminer les placeholders éventuels. 
     * 
     * @param string $str  Le permalink
     * @access protected
     * @return void
     */
    protected function parse($str)
    {
        $this->str_permalink = $str;

        $arr_prov = array();

        $this->int_count = preg_match_all('/:[a-z]/', $str, $arr_prov);

        if($this->int_count)
        {
            //OK, il y a des placeholders
            //Initialisation du tableau qui stockera les clés/valeurs
            $arr = array_pop($arr_prov);

            foreach($arr as $p)
            {
                if(self::checkPlaceholderName($p))
                {
                    $this->arr_keys_values[$p] = null;
                }
                else
                {
                    //Si placeholder inconnu, faux, on lève une exception
                    throw new Exception(sprintf('Bad placeholder %s!', $p));
                }
            }
        }
        else
        {
            //Pas de placeholder, il s’agit donc d’une URL sans variables.
            $this->str_url = $str;
        }
    }

    public function __construct($str)
    {
        $this->parse($str);
    }

    public function setCategories(Category $cat)
    {
    }

    public function setYear($year)
    {
    }

    public function setMonth($month)
    {
    }

    public function setDay($day)
    {
    }

    public function setTitle($title)
    {
    }

    /**
     * Contrôle si toutes les paires clés/valeurs sont formées. 
     * 
     * @access public
     * @return boolean
     */
    public function isOk()
    {
        foreach($this->arr_keys_values as $v)
        {
            if(is_null($v))
            {
                return false;
            }
        }

        return true;
    }


    /**
     * Récupère l’URL créée après l’avoir éventuellement construite.
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        if(is_null($this->str_url))
        {
            $str_out = $this->str_permalink;

            foreach($this->arr_keys_values as $k => $v)
            {
                $str_out = preg_replace(sprintf('/%s/', $k), $v, $str_out);
            }

            $this->str_url = $str_out;
        }
        
        return $this->str_url;
    }



    public function __toString()
    {
        return $this->getUrl();
    }
}
