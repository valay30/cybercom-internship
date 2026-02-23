<?php
class Customer_Block_Account_Edit extends Core_Block_Template
{

    public function _construct()
    {

    }
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Customer/View/Account/edit.phtml");
    }
}

?>