<?php 
    namespace DxlApi\Routes;

    use DxlApi\Abstracts\AbstractRoute as Route;
    use DxlApi\Controllers\ProfileController;
    use DxlApi\Controllers\ProfileEventController;
    
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
                    'permission_callback' => array(new ProfileController, 'validateEndpointResponse')
                ]);

                register_rest_route($this->prefix, '/profile/events', [
                    'method' => \WP_REST_Server::READABLE,
                    'callback' => array(new ProfileController, 'events'),
                    'permission_callback' => array(new ProfileController, 'validateEndpointResponse')
                ]);

                register_rest_route($this->prefix, '/profile/events/create', [
                    'method' => \WP_REST_Server::CREATABLE,
                    'callback' => array(new ProfileEventController, 'create'),
                    'permission_callback' => array(new ProfileEventController, 'validateEndpointResponse')
                ]);
            }
        }
    }
?>