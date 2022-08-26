<?php 
    namespace DxlApi\Abstracts;
    use DxlApi\Services\ApiService;
    use DxlApi\Utilities\LoggerUtility;

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
                $validated = (new ApiService())->validate_bearer_token($request);

                if ( ! $validated ) {
                    $this->api->unauthorized(["message" => "Authorization token is invalid"]);
                    return false;
                }

                if ( ! $request->get_param('user_id') ) {
                    $this->api->not_found(["message" => "User ID is missing"]);
                    return false;
                }

                return true;
            }
        }
    }
?>