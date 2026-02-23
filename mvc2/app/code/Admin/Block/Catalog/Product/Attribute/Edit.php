<?php
class Admin_Block_Catalog_Product_Attribute_Edit extends Core_Block_Template {
    public function _construct(){
        
    }

    public function __construct() {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Product/Attribute/edit.phtml");
    }
}
?>