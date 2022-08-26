<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlMembership\Classes\Repositories\MemberRepository;
    use DxlMembership\Classes\Repositories\MembershipRepository;
    use DxlMembership\Classes\Repositories\MemberProfileRepository;

    /**
     * Services
     */
    use DxlApi\Services\TrainingService;
    use DxlApi\Services\ApiService;
    use DxlApi\Services\RequestService;
    use DxlApi\Services\EventService;
    use DxlApi\Services\MembershipService;

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
             * MembershipService
             *
             * @var DxlApi\Services\MembershipService
             */
            public $membershipService;

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
             * Member profile repository
             *
             * @var \DxlMembership\Classes\Repositories\MemberProfileRepository
             */
            public $memberProfileRepository;

            /**
             * Constructor
             */
            public function __construct()
            {
                $this->memberRepository = new MemberRepository();
                $this->membershipRepository = new MembershipRepository();
                $this->memberProfileRepository = new MemberProfileRepository();
                $this->api = new ApiService();
                $this->eventService = new EventService();
                $this->membershipService = new MembershipService();
            }

            /**
             * fecth profile information
             * 
             * @param \WP_REST_Request $request
             * @return void
             */
            public function index(\WP_REST_Request $request) 
            {
                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();
                
                $events = $this->eventService->fetchAllEventsFromMember($member);
                
                $membership = $this->membershipRepository->select()->where('id', $member->membership)->getRow();
                
                $expiration = $this->membershipService->calculateMembershipExpiration($member, $membership);

                $profileSettings = $this->memberProfileRepository->select()->where('member_id', $member->id)->getRow();

                return $this->api->success([
                    "message" => "Profile found",
                    "data" => [
                        "member" => $member,
                        'membership' => [
                            "id" => $membership->id,
                            "name" => $membership->name,
                            "expiration" => $expiration
                        ],
                        "events" => $events,
                        "profile_settings" => $profileSettings
                    ]
                ]);
            }

            /**
             * Updating member profile action
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function update(\WP_REST_Request $request) {

                if ( ! $request->get_param('member') ) return $this->api->not_found("Could not find member in request body");

                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();
                $updated = $this->memberRepository->update($request->get_param('member'), $member->id);

                return $this->api->success([
                    "message" => "Profile updated",
                    "data" => $request->get_param('member')
                ]);
            }

            /**
             * fetching profile events 
             *
             * @param \WP_REST_Request $request
             */
            public function events(\WP_REST_Request $request) {
                // return $request->get_header('authorization');
                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();
                
                $events = $this->eventService->fetchMemberProfileEvents($member);
                
                return $this->api->success([
                    "message" => "Profile events collected",
                    "data" => [
                        "events" => $events
                    ]
                ]);
            }
        }
    }
?>