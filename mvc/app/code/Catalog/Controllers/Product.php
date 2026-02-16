<?php

class Catalog_Controllers_Product
{
    public function listAction()
    {
        $root = Sdp::getBlock("Page/Root");
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