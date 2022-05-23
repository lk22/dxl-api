<?php 
    namespace DxlApi\Utilities;

    if( !class_exists('RouterUtility') ) 
    {
        class RouterUtility 
        {
            private static $instance;

            public static function getInstance()
            {
                if(!self::$instance) {
                    return new RouterUtility();
                }

                return self::$instance;
            }
        }
    }
?>