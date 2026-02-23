<?php
class Page_Block_Menu extends Core_Block_Template{

    public function getMenuArray(){
        return array(
                "url1" => "category 1",
                "url2" => "category 2"
        );
    }
    
    public function __construct(){
        $this->setTemplate("Page/View/menu.phtml");
    }
}
    
?>