<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;
    use DxlApi\Utilities\LoggerUtility;

    if( !class_exists('ApiService') ) 
    {
        class ApiService implements ServiceInterface
        {

            const HTTP_STATUS_CODE_OK = 200;
            const HTTP_STATUS_CODE_CREATED = 201;
            const HTTP_STATUS_CODE_NOT_FOUND = 404;
            const HTTP_STATUS_CODE_FORBIDDEN = 403;
            const HTTP_STATUS_CODE_UNATHORIZED = 401;
            const HTTP_STATUS_CODE_CONFLICT = 409;

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
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_OK);
                return rest_ensure_response(["code" => self::HTTP_STATUS_CODE_OK, "data" => ["response" => $response]]);
            }

            /**
             * Define created ressource response
             *
             * @param [type] $created
             * @param integer $code
             * @return void
             */
            public function created($created = "Ressource created successfully", $code = self::HTTP_STATUS_CODE_CREATED)
            {
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_CREATED);
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
            public function not_found(?array $data, int $code = self::HTTP_STATUS_CODE_NOT_FOUND) {
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_NOT_FOUND, $data);
                return new \WP_HTTP_Response("Ressource not found", $code);
            }

            /**
             * Forbidden request definition
             *
             * @param string $response
             * @param array|null $data
             * @param integer $code
             * @return void
             */
            public function forbidden($response = "Forbidden request", ?array $data = [], int $code = self::HTTP_STATUS_CODE_FORBIDDEN) {
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_FORBIDDEN);
                return new \WP_HTTP_Response($response, $code);
                // return new \WP_Error($code, $response, $data);
            } 

            /**
             * Return unauthorized response
             *
             * @param array|null $data
             * @param integer $code
             * @return void
             */
            public function unauthorized(?array $data = [], int $code = self::HTTP_STATUS_CODE_UNATHORIZED) {
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_UNATHORIZED, $data);
                return new \WP_HTTP_Response("Unauthorized request catched, you are not allowed to perform this action", $code);
            }

            /**
             * Return conflict status
             *
             * @param string $response
             * @param [type] $code
             * @return void
             */
            public function conflict(string $response = "could not perform your request", int $code = self::HTTP_STATUS_CODE_CONFLICT) {
                LoggerUtility::log("HTTP status code: " . self::HTTP_STATUS_CODE_CONFLICT);
                return new \WP_HTTP_Response("Conflict, " . $response, $code);
            }

            /**
             * return error response
             *
             * @param array|null $data
             * @param integer $code
             * @return void
             */
            public function error(?array $data = [], int $code = 500) {
                LoggerUtility::log("HTTP status code: " . $code);
                return new \WP_Error($code, "Something went wrong", $data);
            }

            /**
             * Validate bearer token
             */
            public function validate_bearer_token($request)
            {
                $token = $request->get_header('Authorization');

                if( empty($token) ) {
                    return $this->unauthorized();
                }
                
                $token = str_replace('Bearer ', '', $token);
                $token = explode(' ', $token);
                $token = $token[0];
                // $token = base64_decode($token);
                // $token = json_encode($token);
                // return $token;

                if( ! $token ) {
                    // LoggerUtility::log("Bearer token invalid", [
                    //     "token" => $token
                    // ]);
                    return $this->unauthorized();
                }

                // LoggerUtility::log("Bearer token validated successfully", [
                //     "token" => $token
                // ]);

                return true;
            }
        }
    }
?>