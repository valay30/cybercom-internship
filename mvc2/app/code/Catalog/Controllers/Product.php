<?php

class Catalog_Controllers_Product extends Core_Controllers_Front
{
    public function listAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $list = Sdp::getBlock("Catalog/Product_List");
        $root->getChild('content')->addChild('list', $list);
        $root->toHtml();
    }
    public function viewAction()
    {
        $root        = Sdp::getBlock("page/root");
        $view        = Sdp::getBlock("catalog/product_View");
        // $media       = Sdp::getBlock("catalog/product_View_Media");
        // $info        = Sdp::getBlock("catalog/product_View_Info");
        // $actions     = Sdp::getBlock("catalog/product_View_Actions");
        // $specs       = Sdp::getBlock("catalog/product_View_Specs");
        // $description = Sdp::getBlock("catalog/product_View_Description");

        // // Add sub-parts
        // $view->addChild("media",       $media);
        // $view->addChild("info",        $info);
        // $view->addChild("actions",     $actions);
        // $view->addChild("specs",       $specs);
        // $view->addChild("description", $description);

        $root->getChild("content")->addChild("view", $view);

        // Pass only the relative path — head.phtml prepends getBaseUrl() automatically
        $root->getChild("head")->addJs("js/Catalog/product.js");
        $root->getChild("head")->addCss("css/Catalog/product.css");

        $root->toHtml();
    }
}
