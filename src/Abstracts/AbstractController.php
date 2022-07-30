<?php 
    namespace DxlApi\Abstracts;
    use DxlApi\Services\ApiService;

    if( !class_exists('AbstractController') ) {
        abstract class AbstractController {
            /**
             * validating endpoint response
             * TODO: automate bearer token validation, only use on specific endpoints
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function validateEndpointResponse(\WP_REST_Request $request) {
                $service = new ApiService();
            }
        }
    }
?>