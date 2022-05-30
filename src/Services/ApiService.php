<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    if( !class_exists('ApiService') ) 
    {
        class ApiService implements ServiceInterface
        {
            public function validate($request): bool {
                foreach($request->get_params() as $key => $param) {
                    if( !isset($param[$key]) ) {
                        return false;
                    }
                }

                return true;
            }

            /**
             * Apply success response
             *
             * @param array $response
             * @return void
             */
            public function success($response) {
                return rest_ensure_response(["code" => 200, "data" => ["response" => $response]]);
            }

            /**
             * Define created ressource response
             *
             * @param [type] $created
             * @param integer $code
             * @return void
             */
            public function created($created, $code = 201)
            {
                return rest_ensure_response([
                    "code" => $code,
                    "message" => $created
                ]);
            }

            /**
             * Perform not found response
             *
             * @param integer $code
             * @return void
             */
            public function not_found(int $code = 404) {
                return new \WP_Error($code, "Rssource not found");
            }

            /**
             * Forbidden request definition
             *
             * @param string $response
             * @param array|null $data
             * @param integer $code
             * @return void
             */
            public function forbidden($response = "Forbidden request", ?array $data = [], int $code = 403) {
                return new \WP_Error($code, $response, $data);
            } 

            /**
             * Return unauthorized response
             *
             * @param array|null $data
             * @param integer $code
             * @return void
             */
            public function unauthorized(?array $data = [], int $code = 401) {
                return new \WP_Error($code, "Unauthorized request catched, you are not allowed to perform this action", $data);
            }
        }
    }
?>