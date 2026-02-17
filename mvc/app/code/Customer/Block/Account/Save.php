<?php
class Customer_Block_Account_Save extends Core_Block_Template
{

    public function _construct()
    {

    }
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Customer/View/Account/save.phtml");
    }
}

?>