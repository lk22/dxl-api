<?php 
    namespace DxlApi\Abstracts;

    if( !class_exists('AbstractRoute') )
    {
        abstract class AbstractRoute 
        {
            protected $prefix = "/dxl/api/v1";
            public abstract function register_endpoints();
        }
    }
?>