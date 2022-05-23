<?php 
    namespace DxlApi\Exceptions;

    if( !class_exists('MemberValidationException') ) 
    {
        class MemberValidationException extends \Exception {}
    }
?>