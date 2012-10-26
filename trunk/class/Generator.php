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
    protected static $arr_file = array();
    protected $str_src = array();
    protected $str_tag_cloud = null;
    protected $str_cat_list = null;
    protected $str_root_cat_list = null;

    public function __construct()
    {
        $this->str_src = Config::getInstance()->getDir()->src;
    }


    public function add(File $file)
    {
        self::$arr_file[$file->getId()] = $file;
    }

    public function get($id){
        return self::$arr_file[$id];
    }

    /**
     * Parcourt l’arborescence à partir de la source définie auparavant.
     *
     * Le parcour se fait tout en créant les bons types de documents (posts, 
     * pages ou fichiers autres) et en créant les tags et maintenant un historique.
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
                        if(isset($f->getHeader()->date))
                        {
                            if(preg_match('/^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})$/', $f->getHeader()->date))
                            {

                                if(strlen($f->getHeader()->date) == 10)
                                {
                                    // on met un horaire arbitraire
                                    History::set($f->getHeader()->date . ' 00:00:00')->setId($f->getId());
                                }
                                else
                                {
                                    History::set($f->getHeader()->date)->setId($f->getId());
                                }
                            }
                            elseif(is_integer($f->getHeader()->date))
                            {
                                // cas de la classe YAML de Symfony qui convertit la date en timestamp
                                History::set(date('Y-m-d H:i:s', $f->getHeader()->date))->setId($f->getId());

                            }
                        }
                        else
                        {
                            History::set(date('Y-m-d H:i:s', $file->getMTime()))->setId($f->getId());
                        }
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
    
    public function renderRootCatList()
    {
        $arr_prov = array();

        foreach(Category::getHier() as $c)
        {
            if(!in_array($c->getRootParent()->getName(), $arr_prov))
            {
                $arr_prov[$c->getRootParent()->getName()] = $c->getRootParent();
            }
        }

        ksort($arr_prov);

        if(is_null($this->str_root_cat_list))
        {
            $t = new Template(Template::ROOT_CATEGORIES);
            $t->assign('root_categories', $arr_prov);
            $this->str_root_cat_list = $t->render();
        }


        return $this->str_root_cat_list;
    }

    protected static function extractInfo(File $f)
    {
        $arr_out = array();

        foreach($f->getHeader() as $k => $v)
        {
            $arr_out[$k] = $v;
        }

        if(Config::getInstance()->getAuthor() && !isset($arr_out['author']))
        {
            $arr_out['author'] = Config::getInstance()->getAuthor();
        }

        $arr_cat = array();
        $arr_cat_prov = array();
        
        if($f->hasCategory())
        {
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
        }

        $arr_tag = array();
        if(isset($f->getHeader()->tags) && count($f->getHeader()->tags))
        {
            foreach($f->getHeader()->tags as $str)
            {
                $l = new Permalink(Config::getInstance()->getPermalinkTag());
                $l->setTitle(Permalink::createSlug($str));
                $arr_tag[] = array(
                    'title' => $str,
                    'url'   => $l->getUrl()
                );
            }
        }


        $str_date_key = date('Y-m-d H:i:s', $f->getDate());
        $id_prev = History::getPrevFor($str_date_key);
        $id_next = History::getNextFor($str_date_key);

        $arr_out['has_prev'] = (boolean) $id_prev;
        $arr_out['has_next'] = (boolean) $id_next;

        if($id_prev)
        {
            $obj_prev = self::$arr_file[$id_prev];
            $arr_out['prev_title'] = $obj_prev->getHeader()->title;
            $arr_out['prev_url'] = $obj_prev->getUrl();
        }

        if($id_next)
        {
            $obj_next = self::$arr_file[$id_next];
            $arr_out['next_title'] = $obj_next->getHeader()->title;
            $arr_out['next_url'] = $obj_next->getUrl();
        }

        $arr_out['content'] = $f->getContent();
        $arr_out['category'] = $f->getCategory();
        $arr_out['categories_breadcrumb'] = $arr_cat;
        $arr_out['tags_list'] = $arr_tag;
        $arr_out['url'] = $f->getUrl();
        $arr_out['date'] = $f->getDate();
        $arr_out['date_rss'] = $f->getDateRss();
        $arr_out['date_atom'] = $f->getDateAtom();
        $arr_out['canonical'] = preg_replace('@/+$@', '', Config::getInstance()->getBase()) . $f->getUrl();
        $arr_out['type'] = $f->isPost() ? 'post' : 'page';

        return (object) $arr_out;
    }

    public function render()
    {
        foreach(self::$arr_file as $f)
        {
            if(!$f->isFile())
            {
                //TODO: C’est probablement ici que je devrai m’occuper des next/prev…
                $t = new Template($f->getHeader()->layout);

                $arr_prov = array();

                foreach(History::getLast() as $id)
                {
                    $arr_prov[] = self::extractInfo(self::$arr_file[$id]);
                }

                foreach(self::extractInfo($f) as $k => $v)
                {
                    $t->assign($k, $v);
                }

                $t->assign('last_posts', $arr_prov);
                $t->assign('tag_cloud', $this->renderTagCloud());
                $t->assign('cat_list', $this->renderCatList());
                $t->assign('root_cat_list', $this->renderRootCatList());
                $t->assign('categories', Category::getHier());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_description', Config::getInstance()->getDescription());
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
                $arr_prov[] = self::extractInfo(self::$arr_file[$id]);
            }

            $t->assign('posts', $arr_prov);
            $t->assign('tag_cloud', $this->renderTagCloud());
            $t->assign('cat_list', $this->renderCatList());
            $t->assign('root_cat_list', $this->renderRootCatList());
            $t->assign('site_name', Config::getInstance()->getName());
            $t->assign('site_description', Config::getInstance()->getDescription());
            $t->assign('site_base', Config::getInstance()->getBase());
            $t->assign('site_meta', Config::getInstance()->getMeta());
            
            file_put_contents(Path::build($tag), $t->render());
        }



        $t = new Template(Template::TAG_INDEX);

        $t->assign('tag_cloud', $this->renderTagCloud());
        $t->assign('cat_list', $this->renderCatList());
        $t->assign('root_cat_list', $this->renderRootCatList());
        $t->assign('site_name', Config::getInstance()->getName());
        $t->assign('site_description', Config::getInstance()->getDescription());
        $t->assign('site_base', Config::getInstance()->getBase());
        $t->assign('site_meta', Config::getInstance()->getMeta());
        
        file_put_contents(Path::buildForRootTag(), $t->render());

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
                    $arr_prov_file[] = self::extractInfo(self::$arr_file[$id]);
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
                $t->assign('root_cat_list', $this->renderRootCatList());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_description', Config::getInstance()->getDescription());
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
                $t->assign('root_cat_list', $this->renderRootCatList());
                $t->assign('site_name', Config::getInstance()->getName());
                $t->assign('site_description', Config::getInstance()->getDescription());
                $t->assign('site_base', Config::getInstance()->getBase());
                $t->assign('site_meta', Config::getInstance()->getMeta());

                file_put_contents(Path::buildForEmptyCategory($str_dir), $t->render());
            }
        }

        
    }
}
