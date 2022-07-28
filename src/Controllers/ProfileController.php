<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlMemberships\Classes\Repositories\MemberRepository;

    /**
     * Services
     */
    use DxlApi\Services\TrainingService;
    use DxlApi\Services\ApiService;
    use DxlApi\Services\RequestService;
    use DxlApi\Services\MemberService;
    use DxlApi\Services\EventService;

    if( !class_exists('ProfileController') ) 
    {
        class ProfileController extends Controller
        {

            /**
             * API Service
             *
             * @var DxlApi\Services\ApiService
             */
            public $ApiService;

            /**
             * EventService
             *
             * @var DxlApi\Services\EventService
             */
            public $eventService;

            /**
             * Constructor
             */
            public function __construct()
            {
                $this->memberRepository = new MemberRepository();
                $this->api = new ApiService();
                $this->eventService = new EventService();
            }

            /**
             * fecth profile information
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function index(\WP_REST_Request $request) 
            {
                $authorized = $this->api->validate_bearer_token($request);
                if( ! $authorized ) {
                    return $this->api->unauthorized();
                }

                if( ! $trequest->get_param('profile_id') )
                {
                    return $this->api->not_found('Could not find id in request');
                }

                $profile = $this->memberRepository->find(
                    $request->get_param('profile_id')
                );
            }
        }
    }
?>