<?php 
    namespace DxlApi\Routes;

    use DxlApi\Abstracts\AbstractRoute as Route;
    use DxlApi\Controllers\ProfileController;
    
    if( !class_exists('ProfileRoutes') ) 
    {
        class ProfileRoutes extends Route
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
                    'callback' => array(new ProfileController, 'index'),
                ]);

                register_rest_route($this->prefix, '/profile/events', [
                    'method' => \WP_REST_Server::READABLE,
                    'callback' => array(new ProfileController, 'events'),
                ]);
            }
        }
    }
?>