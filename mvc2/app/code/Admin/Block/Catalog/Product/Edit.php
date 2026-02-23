<?php
class Admin_Block_Catalog_Product_Edit extends Core_Block_Template {
    public function _construct(){
        
    }

    public function __construct() {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Product/edit.phtml");
    }
}
?>