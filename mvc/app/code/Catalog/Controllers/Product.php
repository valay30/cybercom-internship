<?php

class Catalog_Controllers_Product extends Core_Controllers_Front
{
    public function listAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $list = Sdp::getBlock("Catalog/Product_List");
        $root->getChild('content')->addChild('list',$list);
        $root->toHtml();
    }
    public function viewAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $view = Sdp::getBlock("Catalog/Product_View");
        $root->getChild('content')->addChild('view',$view);
        $root->toHtml();
    }
}

?>