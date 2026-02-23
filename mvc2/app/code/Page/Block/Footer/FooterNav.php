<?php
class Page_Block_Footer_FooterNav extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Footer/footerNav.phtml");
    }

    public function getNavLinks()
    {
        $homeUrl = Sdp::getModel("core/request")->getBaseUrl(); 
        return [
            "$homeUrl"        => "Home",
            "/product" => "Product",
            "/profile" => "Profile",
        ];
    }
}
