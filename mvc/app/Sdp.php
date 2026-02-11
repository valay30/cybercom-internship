<?php

class Sdp{
    public static function run(){
        $front = new Core_Controllers_Front();
        
        $admin = new Core_Controllers_Admin();

        echo "<pre>";
        print_r($front);
        "</pre>";

        echo "<pre>";
        print_r($admin);
        "</pre>";
    }    
}

?>