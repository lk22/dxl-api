<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;
    use DxlMembership\Classes\Repositories\MemberRepository;

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
                    "participants_count" => $tournament->participants_count,
                    "participants" => $participants_data
                ]);
            }
        }
    }