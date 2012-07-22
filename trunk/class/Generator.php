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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Le générateur, parcourt l’arborescence et collecte les données pour ensuite 
 * en faire un rendu.
 */
class Generator
{
    protected $arr_file = array();
    protected $str_src = array();
    protected $str_dest = array();
    protected $str_tag_cloud = null;

    public function __construct()
    {
        $this->str_src = Config::getInstance()->getDir()->src;
        $this->str_dest = Config::getInstance()->getDir()->dest;
        Tag::setBasePath(Config::PATH_TAGS); //TODO: ne doit pas être en dur!!!
    }


    public function add(File $file)
    {
        $this->arr_file[$file->getId()] = $file;
    }

    public function get($id){
        return $this->arr_file[$id];
    }

    /**
     * Parcourt l’arborescence à partir de la source définie auparavant.
     *
     * Le parcour se fait tout en créant les bons types de documents (posts, 
     * pages ou fichiers autres) et en créant les tags.
     */
    public function getData()
    {
        $it = new RecursiveDirectoryIterator("src/");
        foreach(new RecursiveIteratorIterator($it) as $file)
        {
            if($file->isFile())
            {
                $f = new File($file);
                if($f->isPost() || $f->isPage())
                {
                    if(isset($f->getHeader()->tags))
                    {
                        foreach($f->getHeader()->tags as $tag)
                        {
                            Tag::set($tag)->addId($f->getId());
                        }
                    }
                   
                    Category::set($file->getPath())->addId($f->getId());

                    if($f->isPost())
                    {
                        History::set(date('Y-m-d H:i:s', $file->getMTime()))->addId($f->getId());
                    }
                    
                }

                $this->add($f);
            }
        }
    }


    public function renderTagCloud()
    {
        if(is_null($this->str_tag_cloud))
        {
            $t = new Template('tags');
            $t->assign('tags', Tag::getCloud());
            $this->str_tag_cloud = $t->render();
        }

        return $this->str_tag_cloud;
    }


    public function render()
    {

        foreach($this->arr_file as $f)
        {
            $str_dest = $this->str_dest . $f->getDestPath();

            if(!file_exists(dirname($str_dest))){
                mkdir(dirname($str_dest), 0755, true);
            }

            if(!$f->isFile())
            {
                $t = new Template($f->getHeader()->layout);
                $t->setTitle($f->getHeader()->title);
                $t->setContent($f->getContent());
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_base', Config::getInstance()->getBase());
                $t->assign('site_meta', Config::getInstance()->getMeta());
                file_put_contents($str_dest, $t->render());
            }
            else
            {
                copy($f->getSrcPath(), $str_dest);
            }
        }
    }

    public function renderTagPages()
    {
        foreach(Tag::getCloud() as $slug => $tag)
        {

            $t = new Template('tag-page');
            $t->setTitle($tag->getName());
            $arrProv = array();

            foreach($tag->getFileIds() as $id)
            {
                $arrProv[] = $this->arr_file[$id];
            }

            $t->assign('posts', $arrProv);
            $t->assign('tag_cloud', $this->renderTagCloud());
            $t->assign('site_name', Config::getInstance()->getName());
            $t->assign('site_base', Config::getInstance()->getBase());
            $t->assign('site_meta', Config::getInstance()->getMeta());
            
            
            $str_dest = $this->str_dest . $slug . '/index.html';

            if(!file_exists(dirname($str_dest))){
                mkdir(dirname($str_dest), 0755, true);
            }

            file_put_contents($str_dest, $t->render());
        }
    }

    public function renderCategoryPages()
    {
        //TODO: Code this part!!!
        //var_dump(Category::getHier());
    }
}
