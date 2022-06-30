<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\TrainingRepository;

    /**
     * Services
     */
    use DxlApi\Services\TrainingService;
    use DxlApi\Services\ApiService;
    use DxlApi\Services\RequestService;
    use DxlApi\Services\MemberService;
    use DxlApi\Services\EventService;

    if( !class_exists('EventController') ) 
    {
        class EventController extends Controller
        {

            /**
             * Lan Repository
             *
             * @var DxlEvents\Classes\Repositories\LanRepository
             */
            public $lanRepository;

            /**
             * Tournament Repository
             *
             * @var DxlEvents\Classes\Repositories\TournamentRepository
             */
            public $tournamentRepository;

            /**
             * Training repository
             *
             * @var DxlEvents\Classes\Repository\TrainingRepository
             */
            public $trainingRepository;

            /**
             * API Service
             *
             * @var DxlApi\Services\ApiService
             */
            public $ApiService;

            public $eventService;

            /**
             * Constructor
             */
            public function __construct()
            {
                $this->lanRepository = new LanRepository();
                $this->tournamentRepository = new TournamentRepository();
                $this->trainingRepository = new TrainingRepository();
                $this->api = new ApiService();
                $this->eventService = new EventService();
            }

            /**
             * Listing all events
             * @endpoint /events/list
             *
             * @param WP_REST_Request $request
             * @return void
             */
            public function index(\WP_REST_Request $request) {
                $lan = $this->lanRepository->all();
                // $tournaments = $this->lanRepository->all();
                $training = $this->trainingRepository->all();
                $data = [];
                foreach($lan as $l => $event) 
                {
                    $data["events"]["lan"][$l] = [
                        "id" => $event->id,
                        "title" => $event->title,
                        "startdate" => $event->start,
                        "enddate" => $event->end
                    ];
                }

                foreach($training as $t => $event) {
                    $data["events"]["training"][$t] = [
                        "id" => $event->id,
                        "starttime" => date("H", $event->starttime),
                        "endtime" => date("H", $event->endtime),
                        "is_recurring" => $event->is_recurring,
                        "event_day" => $event->event_day
                    ];
                }

                return $this->api->success($data);
            }

            /**
             * fetching event details API
             * @endpoint /events/details
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function details(\WP_REST_Request $request) 
            {

            }

            /**
             * search for events API
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function search(\WP_REST_Request $request) 
            {
                $filter = $request->get_param('filters');

                $events = $this->eventService->getFilteredEvents($filter);
                return $this->api->success($events);
            }

            /**
             * Participate event
             *
             * @param \Wp_REST_Request $request
             * @return void
             */
            public function participate(\WP_REST_Request $request)
            {
                $params = $request->get_body_params();
                $memberService = new MemberService();
                
                $eventService = $this->eventService($params["type"]);

                $participant = $memberService->validateParticipant($params["email"], $params["gamertag"]);

                // return $participant;
                if( $participant == false ) {
                    return new \WP_Error(404, 'Vi kunne ikke registrere dig som deltager, du findes enten ikke i vores medlems kartotek eller er ikke betalt medlem', [
                        "data" => $params
                    ]);
                }

                if( !$participant->is_payed ) {
                    return new \WP_Error(403, 'Du skal være betalt medlem af foreningen for at deltage', [
                        "data" => $participant
                    ]);
                }

                $event = $eventService->getEvent($params["event"], $params["type"]);

                $participated = $eventService->participate($participant, $event);

                if (!$participated) {
                    return new \WP_Error(500, 'Du er allerede tilmeldt denne trænings begivenhed');
                }  

                return $this->api->created([
                    "code" => 201,
                    "response" => "Du er nu tilmeldt begivenheden"
                ]);
            }

            /**
             * unparticipate event 
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function unparticipate(\WP_REST_Request $request) 
            {
                $params = $request->get_params();

                $eventService = $this->eventService($params["type"]);
                $event = $eventService->getEvent($params["event"]);

                if( !$event ) {
                    return new \WP_Error(404, 'Event not found', ["data" => $params]);
                }

                if( !$eventService->unparticipate($event, $params["member"]) ) {
                    return new \WP_Error(500, "Could not unparticipate event, something went wrong", ["data" => $event]);
                }

                return $this->api->success("Du er nu afmeldt begivenheden");
            }

            /**
             * get event service helper
             *
             * @param [type] $type
             * @return void
             */
            private function eventService($type)
            {
                switch($type)
                {
                    case "training":
                        $eventService =  new TrainingService();
                        break;

                    case "tournament":
                        // add tournament service
                        break;

                    case "lan":
                        $eventService = new LanService();
                        break;
                }

                return $eventService;
            }
        }
    }
?>