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
use DirectoryIterator;

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
        $obj_iter = new RecursiveDirectoryIterator(Path::getSrc());
        foreach(new RecursiveIteratorIterator($obj_iter) as $file)
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
            $t = new Template(Template::TAGS);
            $t->assign('tags', Tag::getCloud());
            $this->str_tag_cloud = $t->render();
        }

        return $this->str_tag_cloud;
    }

    public function renderCatList()
    {
        if(is_null($this->str_cat_list))
        {
            $t = new Template(Template::CATEGORIES);
            $t->assign('categories', Category::getHier());
            $this->str_cat_list = $t->render();
        }

        return $this->str_cat_list;
    }

    protected static function extractInfo(File $f)
    {
        $arr_out = array();

        foreach($f->getHeader() as $k => $v)
        {
            $arr_out[$k] = $v;
        }

        $arr_cat = array();
        $arr_cat_prov = array();

        foreach($f->getCategory()->getNode() as $str_node)
        {
            $l = new Permalink(Config::getInstance()->getPermalinkCategory());
            $arr_cat_prov[] = $str_node;
            $l->setTitle(implode('/', $arr_cat_prov));
            $arr_cat[] = array(
                'title' => Config::getInstance()->getCategory($str_node),
                'url'   => $l->getUrl()
            );
        }

        $arr_out['content'] = $f->getContent();
        $arr_out['category'] = $f->getCategory();
        $arr_out['categories_breadcrumb'] = $arr_cat;
        $arr_out['url'] = $f->getUrl();
        $arr_out['type'] = $f->isPost() ? 'post' : 'page';

        return (object) $arr_out;
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
                
                $arr_prov = array();

                foreach(History::getLast() as $id)
                {
                    $arr_prov[] = self::extractInfo($this->arr_file[$id]);
                }

                $t->assign('last_posts', $arr_prov);
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('cat_list', $this->renderCatList());
                $t->assign('categories', Category::getHier());
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
            $t = new Template(Template::TAG_PAGE);
            $t->assign('title', $tag->getName());


            $arr_prov = array();

            foreach($tag->getFileIds() as $id)
            {
                $arr_prov[] = self::extractInfo($this->arr_file[$id]);
            }

            $t->assign('posts', $arr_prov);
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
        $arr_tree = Category::getTree();
        
        foreach(Category::getHier() as $str_slug => $obj_cat)
        {
            if($str_slug != '/') // cas particulier des articles sans catégorie
            {
                $t = new Template(Template::CATEGORY_PAGE);
                $t->assign('title', $obj_cat->getName());

                $arr_prov_file = array();

                foreach($obj_cat->getFileIds() as $id)
                {
                    $arr_prov_file[] = self::extractInfo($this->arr_file[$id]);
                }

                $arr_prov_cat = array();

                foreach($obj_cat->getNode() as $str_node)
                {
                    $arr_prov_cat[] = sprintf('["%s"]', $str_node);
                }

                $arr_prov_cat = array_keys(eval(sprintf('return $arr_tree%s;', implode('', $arr_prov_cat))));

                $arr_prov_cat2 = array();

                foreach($arr_prov_cat as $str)
                {
                    $arr_prov_cat2[] = (object) array(
                        'url' => $obj_cat->getUrl() . $str,
                        'title' => Config::getInstance()->getCategory($str)
                    );
                }
                
                $t->assign('posts', $arr_prov_file);
                $t->assign('cats', $arr_prov_cat2);
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('cat_list', $this->renderCatList());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_base', Config::getInstance()->getBase());
                $t->assign('site_meta', Config::getInstance()->getMeta());

                file_put_contents(Path::build($obj_cat), $t->render());
            }
        }

        $arr_dir = array();

        $obj_iter = new RecursiveDirectoryIterator(Path::getDestCategory(), RecursiveDirectoryIterator::KEY_AS_PATHNAME);
        foreach(new RecursiveIteratorIterator($obj_iter, RecursiveIteratorIterator::CHILD_FIRST) as $file)
        {
            if($file->isDir())
            {
                if(!in_array(dirname($file->__toString()), $arr_dir))
                {
                    $arr_dir[] = dirname($file->__toString());
                }
            }
        }

        
        foreach($arr_dir as $str_dir)
        {
            $obj_dir_iter = new DirectoryIterator($str_dir);

            $bool_has_index = false;

            $arr_last = array();

            foreach($obj_dir_iter as $obj_file)
            {
                if($obj_file->isFile() && ($obj_file->getFileName() == 'index.html'))
                {
                    $bool_has_index = true;
                }

                if($obj_file->isDir() && !$obj_file->isDot())
                {
                    $arr_last[] = (object) array(
                        'url' =>  preg_replace(sprintf('@^%s@', Path::getDest()), '', $obj_file->getPathname()), //TODO: Avoir un moyen de récupérer l’URL proprement
                        'title' => Config::getInstance()->getCategory($obj_file->getFileName())
                    );
                }
            }

            if(!$bool_has_index)
            {
                $str_slug_cat = preg_replace(sprintf('@^%s@', Path::getDestCategory()), '', $str_dir);
                $t = new Template(Template::CATEGORY_PAGE);
                if(Config::getInstance()->hasCategory($str_slug_cat))
                {
                    $t->assign('title', Config::getInstance()->getCategory($str_slug_cat));
                }
                else
                {
                    $t->assign('title', null);
                }
                $t->assign('posts', array());
                $t->assign('cats', $arr_last);
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('cat_list', $this->renderCatList());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_base', Config::getInstance()->getBase());
                $t->assign('site_meta', Config::getInstance()->getMeta());

                file_put_contents(Path::buildForEmptyCategory($str_dir), $t->render());
            }
        }

        
    }
}
