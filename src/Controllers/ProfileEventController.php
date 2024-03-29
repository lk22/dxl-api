<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlMembership\Classes\Repositories\MemberRepository;

    /**
     * Services
     */
    use DxlApi\Services\TrainingService;
    use DxlApi\Services\ApiService;
    use DxlApi\Services\EventService;

    if( !class_exists('ProfileEventController') ) 
    {
        class ProfileEventController extends Controller
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
             * Constructor
             */
            public function __construct()
            {
                $this->memberRepository = new MemberRepository();
                $this->api = new ApiService();
                $this->eventService = new EventService();
            }

            /**
             * Create new profile event
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function create(\WP_REST_Request $request) {
                if ( ! $request->get_param('event') ) return $this->api->not_found();

                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();

                $created = $this->eventService->createEvent(
                    $request->get_param('type'),
                    $request->get_param('event')
                );

                if( ! $created ) return $this->api->conflict("Conflict, could not create event ressource");
                return $this->api->created();
            }

            /**
             * updating profile event
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function update(\WP_REST_Request $request) {
                if ( ! $request->get_param('event') ) return $this->api->not_found("Event information not found in request, cannot proceed");

                $member = $this->memberRepository->select()->where('user_id', $request->get_param('user_id'))->getRow();

                $updated = $this->eventService->updateEvent(
                    $request->get_param('type'),
                    $request->get_param('event')
                );

                if( ! $updated ) return $this->api->conflict("Conflict, could not update event ressource");
                return $this->api->success("Event updated successfully");
            }
        }
    }
?>