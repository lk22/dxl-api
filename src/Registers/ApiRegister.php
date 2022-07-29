<?php 
    namespace DxlApi\Registers;

    use DxlApi\Routes\EventRoutes;
    use DxlApi\Routes\HomeRoutes;
    use DxlApi\Routes\ProfileRoutes;

    if( !class_exists('ApiRegister') ) 
    {
        class ApiRegister 
        {

            public function __construct()
            {
                add_action('rest_api_init', [$this, 'registerRoutes']);
                add_action('rest_api_init', function() {
                    new EventRoutes();
                    new HomeRoutes();
                    new ProfileRoutes();
                });
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
                $this->profileRoutes = new ProfileRoutes();
            }
        }
    }
?>