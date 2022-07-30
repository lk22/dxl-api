<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlMembership\Classes\Repositories\MemberRepository;
    use DxlMembership\Classes\Repositories\MembershipRepository;

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
             * Member repository
             *
             * @var \DxlMembership\Classes\Repositories\MemberRepository
             */
            public $memberRepository;

            /**
             * Membership repository
             *
             * @var \DxlMembership\Classes\Repositories\MembershipRepository
             */
            public $membershipRepository;

            /**
             * Constructor
             */
            public function __construct()
            {
                $this->memberRepository = new MemberRepository();
                $this->membershipRepository = new MembershipRepository();
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
                $this->api->validate_bearer_token($request);

                if( ! $request->get_param('user_id') )
                {
                    return $this->api->not_found();
                }

                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();
                // return $member;
                
                if( ! $member ) 
                {
                    return $this->api->not_found();
                }
                
                $membership = $this->membershipRepository->select()->where('id', $member->membership)->getRow();
                return $this->api->success([
                    "code" => 200,
                    "message" => "Profile found",
                    "data" => [
                        "member" => $member,
                        'membership' => $membership
                    ]
                ]);
            }
        }
    }
?>