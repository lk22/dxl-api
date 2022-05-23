<?php 

/**
 * Plugin Name: DXL API Module
 * Description: WordPress extended API for DXL module functionality
 * Author: Leo Knudsen
 * Version: 1.0.0
 */

 if(!defined('ABSPATH')) {
    exit;
 }

 require_once dirname(__FILE__) . "/vendor/autoload.php";
 require dirname(__FILE__) . "/src/Api.php";

new Api();

?>