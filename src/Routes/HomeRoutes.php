<?php 
    namespace DxlApi\Routes;
    use DxlApi\Abstracts\AbstractRoute as Route;

    if( !class_exists('HomeRoutes') ) {
        class HomeRoutes extends Route 
        {
            /**
             * Undocumented function
             */
            public function __construct()
            {
                add_action('rest_api_init', [$this, 'register_endpoints']);
            }

            /**
             * Registering home routes
             *
             * @return void
             */
            public function register_endpoints()
            {
                register_rest_route($this->prefix, '/home', [
                    'method' => \WP_REST_Server::READABLE,
                    'callback' => array($this, 'index'),
                ]);
            }

            /**
             * testing index endpoint method
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function index(\WP_REST_Request $request) {
                return "ok!";
            }
        }
    }
?>