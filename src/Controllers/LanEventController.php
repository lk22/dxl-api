<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;
    use DxlEvents\Classes\Repositories\LanParticipantRepository;
    use DxlMembership\Classes\Repositories\MemberRepository;

    use DxlApi\Services\EventService;
    use DxlApi\Services\MemberService;

    /**
     * Exceptions
     */
    use DxlEvents\Classes\Exceptions\AllreadyParticipatedException;

    use DxlApi\Services\ApiService;

    if( !defined('ABSPATH') ) {
        exit;
    }

    if( !class_exists('LanEventController') ) 
    {
        class LanEventController extends Controller 
        {
            /**
             * LAN Repository
             *
             * @var DxlEvents\Classes\Repositories\LanRepository
             */
            protected $lanRepository;

            /**
             * Tournament repository
             *
             * @var DxlEvents\Classes\Repositories\TournamentRepository
             */
            protected $tournamentRepository;

            /**
             * participant repository
             *
             * @var DxlEvents\Classes\Repositories\ParticipantRepository
             */
            protected $participantRepository;

            /**
             * LAN participant repository
             *
             * @var DxlEvents\Classes\Repositories\LanParticipantRepository
             */
            protected $lanParticipantRepository;

            /**
             * participant repository
             *
             * @var DxlMembershipts\Classes\Repositories\MemberRepository
             */
            protected $memberRepository;

            /**
             * Api service
             *
             * @var DxlApi\Services\ApiService
             */
            protected $apiService;

            /**
             * Constructor
             */
            public function __construct() 
            {
                $this->lanRepository = new LanRepository();
                $this->tournamentRepository = new TournamentRepository();
                $this->participantRepository = new ParticipantRepository();
                $this->lanParticipantRepository = new LanParticipantRepository();
                $this->memberRepository = new MemberRepository();
                $this->api = new ApiService();
            }

            /**
             * fethcing lan attached tournament information
             *
             * @api /event/lan/details/tournaments/detail
             * @param \WP_REST_Request $request
             * @return void
             */
            public function tournament(\WP_REST_Request $request) 
            {
                $eventTournament = $request->get_params()["tournament"];
                $event = $request->get_params()["event"];

                // return $event;

                $tournament = $this->tournamentRepository   
                    ->select()
                    ->where('id', $eventTournament)
                    ->whereAnd('has_lan', 1)
                    ->whereAnd('lan_id', $event)
                    ->getRow();

                $participants = $this->participantRepository->findByEvent($tournament->id);

                $participants_data = [];
                
                foreach ( $participants as $p => $participant ) {
                    $member = $this->memberRepository->find($participant->member_id);
                    $participants_data[$p] = [
                        "name" => $member->name,
                        "gamertag" => $member->gamertag,
                    ];
                }

                return $this->api->success([
                    "title" => $tournament->title,
                    "start" => date("d F Y", $tournament->start),
                    "starttime" => date("H:i", $tournament->starttime),
                    "end" => date("d F Y", $tournament->end),
                    "endtime" => date("H:i", $tournament->endtime),
                    "description" => $tournament->description,
                    "participants_count" => $tournament->participants_count,
                    "participants" => $participants_data
                ]);
            }

            /**
             * LAN event participate API
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function participate(\WP_REST_Request $request)
            {
                // TODO: send participated mail to participant
                // TODO: send mail to event manager about the participant
                
                $eventService = new EventService();
                $memberService = new MemberService();
                
                $breakfast = ($request->get_param("breakfast") == "on") ? 1 : 0;
                $dinner_friday = ($request->get_param("dinner_friday") == "on") ? 1 : 0;
                $dinner_saturday = ($request->get_param("dinner_saturday") == "on") ? 1 : 0;

                $participantExists = $eventService->getExistingParticipant($request->get_param("event"), $request->get_param("gamertag"));

                if( $participantExists ) {
                    return $this->api->conflict("Du er allerede tilmeldt denne begivenhed");
                }

                $gamertag = $request->get_param("gamertag");

                $member = $this->memberRepository->select()->where('gamertag', "'$gamertag'")->getRow();

                $participant = $this->lanParticipantRepository->create([
                    "event_id" => $request->get_param('event'),
                    "member_id" => $member->id,
                    "name" => $member->name,
                    "gamertag" => $member->gamertag,
                    "has_saturday_breakfast" => $breakfast,
                    "has_saturday_breakfast" => $breakfast,
                    "has_sunday_breakfast" => $breakfast,
                    "has_sunday_breakfast" => $breakfast,
                    "has_friday_lunch" => $dinner_friday,
                    "has_saturday_dinner" => $dinner_saturday,
                    "participated" => time()
                ]);
                
                if( !$participant ) {
                    return $this->api->conflict("Der skete en fejl, kunne ikke tilmelde dig begivenheden");
                }

                $seats_updated = $eventService->removeAvailableSeat($request->get_param("event"));

                return $this->api->created("du er nu tilmeldt begivenheden, du modtager en mail fra os vedr begivenheden");
            }

            /**
             * Participate lan tournament
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function participateTournament(\WP_REST_Request $request)
            {
                $tournament = $request->get_param('tournament');
                $member = $request->get_param('member');
                $event = $request->get_param('event');

                $participant = $this->memberRepository->find($member);

                $participated = $this->participantRepository->create([
                    "member_id" => $particpant->id,
                    "name" => $participant->name,
                    "gamertag" => $participant->gamertag,
                    "email" => $participant->email,
                    "event_id" => $tournament,
                    "lan_id" => $event
                ]);

                if( !$participated ) {
                    return $this->api->conflict('Noget gik galt, kunne ikke deltage i turneringen');
                }

                return $this->api->created("Du er nu tilmeldt turneringen");
            }
        }
    }