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
 header("Access-Control-Allow-Origin: *");

 require_once dirname(__FILE__) . "/vendor/autoload.php";
 require dirname(__FILE__) . "/src/Api.php";

if( ! function_exists('add_user_id_to_jwt') ) {
   function add_user_id_to_jwt($token, $user) {
      $token['user_id'] = $user->ID;
      return $token;
   }
}

add_filter('jwt_auth_token_before_dispatch', 'add_user_id_to_jwt', 10, 2);

new Api();

?>