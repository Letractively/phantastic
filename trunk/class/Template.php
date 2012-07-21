<?php

namespace Malenki\Phantastic;

class Template 
{
    protected $arr_data = array();
    protected $str_tmpl = null;

    public function __construct($tmpl)
    {
        $this->str_tmpl = $tmpl;
    }

    public function setTitle($str)
    {
        $this->assign('title', $str);
    }

    public function setContent($str)
    {
        $this->assign('content', $str);
    }

    public function assign($key, $value)
    {
        $this->arr_data[$key] = $value;
    }

    protected function partial($str)
    {
        $data = (object) $this->arr_data;
        require(
            sprintf(
                '%s%s.phtml',
                Config::getInstance()->getDir()->template,
                $str
            )
        );
    }

    public function render()
    {
        $data = (object) $this->arr_data;

        ob_start();
        require(
            sprintf(
                '%s%s.phtml',
                Config::getInstance()->getDir()->template,
                $this->str_tmpl
            )
        );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
