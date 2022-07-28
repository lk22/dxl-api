<?php 
    namespace DxlApi\Routes;

    use DxlApi\Abstracts\AbstractRoute as Route;
    
    if( !class_exists('EventRoutes') ) 
    {
        class EventRoutes extends Route
        {
            /**
             * Constructing Event APIS
             */
            public function __construct() 
            {
                add_action('rest_api_init', [$this, 'register_endpoints']);
            }

            /**
             * Registering event ednpoints
             *
             * @return void
             */
            public function register_endpoints()
            {
                register_rest_route($this->prefix, '/profile', [
                    'method' => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'index'),
                ]);
            }
        }
    }
?>