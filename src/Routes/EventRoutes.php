<?php 
    namespace DxlApi\Routes;

    use DxlApi\Abstracts\AbstractRoute as Route;
    use DxlApi\Controllers\EventController;
    
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
                // event participate endpoint
                
                register_rest_route($this->prefix, '/event/list', [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [new EventController, 'index']
                ]);

                // event details endpoint
                register_rest_route($this->prefix, '/event/details', [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [new EventController, 'details']
                ]);

                // participate event endpoint
                register_rest_route($this->prefix, '/event/participate', [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [new EventController, 'participate']
                ]);

                // unparticipate event endpoint
                register_rest_route($this->prefix, '/event/unparticipate', [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [new EventController, 'unparticipate']
                ]);
            }
        }
    }
?>