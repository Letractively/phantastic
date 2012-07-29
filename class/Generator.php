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
    protected $str_tag_cloud = null;
    protected $str_cat_list = null;

    public function __construct()
    {
        $this->str_src = Config::getInstance()->getDir()->src;
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
                    //Avant tout, tester si ça doit être publié ou pas…
                    if(isset($f->getHeader()->published))
                    {
                        if(!$f->getHeader()->published)
                        {
                            continue;
                        }
                    }
                   
                   
                    if(isset($f->getHeader()->tags))
                    {
                        foreach($f->getHeader()->tags as $tag)
                        {
                            Tag::set($tag)->addId($f->getId());
                        }
                    }
                    


                    if($f->isPost())
                    {
                        Category::set($file->getPath())->addId($f->getId());
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

    public function renderCatList()
    {
        if(is_null($this->str_cat_list))
        {
            $t = new Template('categories');
            $t->assign('categories', Category::getHier());
            $this->str_cat_list = $t->render();
        }

        return $this->str_cat_list;
    }

    public function render()
    {
        foreach($this->arr_file as $f)
        {
            if(!$f->isFile())
            {
                //TODO: C’est probablement ici que je devrai m’occuper des next/prev…
                $t = new Template($f->getHeader()->layout);
                
                foreach($f->getHeader() as $k => $v)
                {
                    $t->assign($k, $v);
                }

                $t->setContent($f->getContent());
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('cat_list', $this->renderCatList());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_base', Config::getInstance()->getBase());
                $t->assign('site_meta', Config::getInstance()->getMeta());
                file_put_contents(Path::build($f), $t->render());
            }
            else
            {
                copy($f->getSrcPath(), Path::build($f));
            }
        }
    }

    public function renderTagPages()
    {
        foreach(Tag::getCloud() as $tag)
        {
            $t = new Template('tag-page');
            $t->assign('title', $tag->getName());
            $arrProv = array();

            foreach($tag->getFileIds() as $id)
            {
                $arrProv[] = $this->arr_file[$id];
            }

            $t->assign('posts', $arrProv);
            $t->assign('tag_cloud', $this->renderTagCloud());
            $t->assign('cat_list', $this->renderCatList());
            $t->assign('site_name', Config::getInstance()->getName());
            $t->assign('site_base', Config::getInstance()->getBase());
            $t->assign('site_meta', Config::getInstance()->getMeta());
            
            file_put_contents(Path::build($tag), $t->render());
        }
    }

    public function renderCategoryPages()
    {
        //TODO: Code this part!!!
        //var_dump(Category::getHier());
    }
}
