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
                if( ! $request->get_header('Authorization') ) {
                    return false;
                }

                if (  $request->get_param('user_id') ) {
                    return false;
                }

                $validated = (new ApiService())->validate_bearer_token($request);
                if ( ! $validated ) {
                    return false;
                }

                return true;
            }
        }
    }
?>