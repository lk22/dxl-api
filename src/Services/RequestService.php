<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    if( !class_exists('RequestService') ) 
    {
        class RequestService implements ServiceInterface
        {
            /**
             * Request object
             *
             * @var [type]
             */
            protected $request;

            /**
             * get request arguments list
             *
             * @return void
             */
            public function getArguments()
            {
                return $this->request->get_params();
            }

            /**
             * Get single argument
             *
             * @param string $key
             * @return void
             */
            public function getArgument(string $key)
            {
                return (isset($this->request->get_params()[$key])) 
                    ? $this->request->get_params()[$key] 
                    : "";
            }
        }
    }
?>