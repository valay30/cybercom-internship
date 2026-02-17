<?php

class Admin_Controllers_Catalog_Product
{
    public function newAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $new = Sdp::getBlock("Admin/Catalog_Product_New");
        $root->getChild('content')->addChild('new',$new);
        $root->toHtml();
    }
    public function editAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $edit = Sdp::getBlock("Admin/Catalog_Product_Edit");
        $root->getChild('content')->addChild('edit',$edit);
        $root->toHtml();
    }
    public function deleteAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $delete = Sdp::getBlock("Admin/Catalog_Product_Delete");
        $root->getChild('content')->addChild('delete',$delete);
        $root->toHtml();
    }
    public function listAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $list = Sdp::getBlock("Admin/Catalog_Product_List");
        $root->getChild('content')->addChild('list',$list);
        $root->toHtml();
    }
}

?>