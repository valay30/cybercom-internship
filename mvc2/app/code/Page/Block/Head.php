<?php
class Page_Block_Head extends Core_Block_Template
{
    protected $_js = [];
    protected $_css = [];

    public function __construct()
    {
        $this->setTemplate("Page/View/head.phtml");
        $this->addJs("js/default.js")
            ->addJs("js/default1.js")
            ->addJs("js/home.js")
            ->addCss("css/header.css")
            ->addCss("css/footer.css")
            ->addCss("css/default1.css")
            ->addCss("css/home.css");
    }

    public function addJs($file)
    {
        $this->_js[] = $file;
        return $this;
    }

    public function getJs()
    {
        return $this->_js;
    }

    public function addCss($file)
    {
        $this->_css[] = "$file";
        return $this;
    }
    public function getCss()
    {
        return $this->_css;
    }
}
