<?php
class Catalog_Block_Product_View extends Core_Block_Template
{
    public function _construct()
    {
        
    }
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/view.phtml");
    }
    public function getProduct(){
        $product = Sdp::getModel("Catalog/Product");
        $product->addData(
            [
                "product_id"=>1,
                "name"=>"Dell laptop 001",
                "url"=>"Dell-laptop-001"
            ]
        );
        echo "<pre>";
        print_r($product);
        return $product;
    }
}

?>