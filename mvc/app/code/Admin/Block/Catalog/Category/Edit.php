<?php
class Admin_Block_Catalog_Category_Edit extends Core_Block_Template {
    public function _construct(){
        
    }

    public function __construct() {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Category/edit.phtml");
    }
}
?>