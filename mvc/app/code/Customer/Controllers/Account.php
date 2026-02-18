<?php
class Customer_Controllers_Account extends Core_Controllers_Front{
    public function indexAction(){
        $root = Sdp::getBlock("Page/Root");
        $index = Sdp::getBlock("Customer/Account_index");
        $root->getChild('content')->addChild('index',$index);
        $root->toHtml();
    }
    public function addressAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $address = Sdp::getBlock("Customer/Account_address");
        $root->getChild('content')->addChild('address',$address);
        $root->toHtml();
    }
    public function editAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $edit = Sdp::getBlock("Customer/Account_edit");
        $root->getChild('content')->addChild('edit',$edit);
        $root->toHtml();
    }
    public function saveAction()
    {
        $root = Sdp::getBlock("Page/Root");
        $save = Sdp::getBlock("Customer/Account_save");
        $root->getChild('content')->addChild('save',$save);
        $root->toHtml();
    }
}

?>