<?php 
    namespace DxlApi\Registers;

    use DxlApi\Routes\EventRoutes;
    use DxlApi\Routes\HomeRoutes;

    if( !class_exists('ApiRegister') ) 
    {
        class ApiRegister 
        {

            public function __construct()
            {
                add_action('rest_api_init', [$this, 'registerRoutes']);
            }

            public function register()
            {
                // register apis
                // register router
                $this->registerRoutes();
            }

            public function registerRoutes(){
                $this->eventRoutes = new EventRoutes();
                $this->homeRoutes = new HomeRoutes();
            }
        }
    }
?>