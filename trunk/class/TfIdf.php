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
use Exception;


class TfIdf
{
    protected static $arr_documents = array();

    protected $arr_tf = array();
    protected $arr_tfidf = array();





    public static function getCount()
    {
        return count(self::$arr_documents);
    }



    public static function isEmpty()
    {
        return self::getCount() == 0;
    }



    public static function set($int_id, $str_text)
    {
        $tfIdf = new self($str_text);
        $tfIdf->addToDocumentList($int_id);

        return self::$arr_documents[$int_id];
    }

    public static function get($int_id)
    {
        return self::$arr_documents[$int_id];
    }



    public static function idf($str_term)
    {
        $int_count = 0;

        if(self::isEmpty())
        {
            throw new Exception('Can not calculate Inverse Document Frequency because there are not document.');
        }

        foreach(self::$arr_documents as $obj)
        {
            if($obj->hasTerm($str_term))
            {
                $int_count++;
            }
        }

        return log($int_count / self::getCount());
    }


    public static function distance($int_id1, $int_id2)
    {
        $arr_1 = self::get($int_id1)->getTermsAndFrequencies();
        $arr_2 = self::get($int_id2)->getTermsAndFrequencies();

        $float_missing_value = 0.0001;
        $float_dist = 0;
        
        $arr_tokens = array_keys(array_merge($arr_1, $arr_2));

        foreach($arr_tokens as $str_token)
        {
            if(!isset($arr_1[$str_token]))
            {
                $arr_1[$str_token] = $float_missing_value;
            }
        
            if(!isset($arr_2[$str_token]))
            {
                $arr_2[$str_token] = $float_missing_value;
            }
    
            $float_dist += pow(($arr_1[$str_token] - $arr_2[$str_token]), 2);
        }
        return $float_dist;
    }



    public function __construct($str_text)
    {
        $str_text = preg_replace("/([!?.,\*\"]{1})/", " \\1 ", $str_text);
        $arr_tokens = preg_split("/\s+/", $str_text);
        $int_count_tokens = count($arr_tokens);

        // on remplit le tableau Term Frequency
        foreach($arr_tokens as $str_token)
        {
            if(isset($this->arr_tf[$str_token]))
            {
                $this->arr_tf[$str_token]++;
            }
            else
            {
                $this->arr_tf[$str_token] = 1;
            }
        }

        // maintenant, calculons la fréquence pour chaque terme.
        foreach($this->arr_tf as $k => $v)
        {
            $this->arr_tf[$k] = $v / $int_count_tokens;
        }

        unset($str_text);
        unset($arr_tokens);
    }



    public function hasTerm($str_term)
    {
        return array_key_exists($str_term, $this->arr_tf);
    }



    public function getTermsAndFrequencies()
    {
        if(count($this->arr_tfidf) == 0)
        {
            foreach($this->arr_tf as $str_term => $float_tf)
            {
                $this->arr_tfidf[$str_term] = $this->calculate($str_term);
            }
        }

        return $this->arr_tfidf;
    }



    public function tf($str_term)
    {
        return $this->arr_tf[$str_term];
    }



    public function calculate($str_term)
    {
        return abs($this->tf($str_term) * self::idf($str_term));
    }



    public function addToDocumentList($int_id)
    {
        self::$arr_documents[$int_id] = $this;
    }
}
