<?php 

use DxlApi\Interfaces\ServiceInterface;

if( !class_exists('Api') ) {
    class Api {

        /**
         * Core API constructor
         */
        public function __construct(){
            return (new \DxlApi\Registers\ApiRegister())->register();
        }
    }
}

?>