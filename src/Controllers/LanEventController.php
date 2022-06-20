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

    /**
     * Services
     */
    use DxlApi\Services\EventService;
    use DxlApi\Services\MemberService;
    use DxlApi\Services\ApiService;

    /**
     * Emails
     */
    use DxlEvents\Classes\Mails\EventParticipatedMail;
    use DxlEvents\Classes\Mails\LanEventParticipatedMail;
    /**
     * Exceptions
     */
    use DxlEvents\Classes\Exceptions\AllreadyParticipatedException;

    
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
                // return $request->get_params();
                $eventService = new EventService();
                $memberService = new MemberService();

                $seatedMembers = $request->get_param('members');

                $breakfast = ($request->get_param("breakfast") == "on") ? 1 : 0;
                $dinner_friday = ($request->get_param("dinner_friday") == "on") ? 1 : 0;
                $dinner_saturday = ($request->get_param("dinner_saturday") == "on") ? 1 : 0;

                $participantExists = $eventService->getExistingParticipant(
                    $request->get_param("event"), 
                    $request->get_param("gamertag")
                );

                $lanEvent = $this->lanRepository->find($request->get_param('event'));
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

                $participantMail = (new EventParticipatedMail(
                    $member, 
                    $lanEvent, 
                    $seatedMembers, 
                    $request->get_param('message')
                ))->setSubject('Tilmelding ' . $member->gamertag . ')')
                    ->setReciever($member->email)
                    ->send();

                $participantNotification = (new LanEventParticipatedMail(
                    $member, 
                    $lanEvent, 
                    $seatedMembers, 
                    $request->get_param('message')
                ))->setSubject("Ny tilmelding, " . $member->gamertag)
                    ->setReciever($member->email)
                    ->send();

                $seats_updated = $eventService->removeAvailableSeat($request->get_param("event"));

                return $this->api->created("du er nu tilmeldt begivenheden, du modtager en mail fra os vedr begivenheden");
            }

            /**
             * Unparticipate LAN event 
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function unparticipate(\WP_REST_Request $request) 
            {
                $event = $requst->get_param('event');
                $member = $request->get_param('member');

                $member = $this->memberRepository->find($member);
                
                $tournaments = $this->tournamentRepository->select()->where('lan_id', $event)->get();
                foreach($tournaments as $tournament) {
                    $pcount = $this->tournamentRepository
                        ->select(['participant_count'])
                        ->where('id', $tournament->id)
                        ->getRow();

                    $participant = $this->participantRepository
                        ->select()
                        ->where('member_id', $member)
                        ->andWhere('id', $tournament->id)
                        ->getRow();

                    // if the participant exists on the tournament, remove the participant
                    if( $participant ) {
                        $this->participantRepository->delete($participant->id);

                        $this->tournamentRepository->update(
                            ['participants_count' => $tournament],
                            $participant->id
                        );
                    }
                }

                $this->lanParticipantRepository->removeFromEvent($member->id, $event);

                return $this->api->success('Du er nu fjernet fra deltagerlisten');
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

                $participant = $this->memberRepository->select()->where('user_id', $member)->getRow();
                $tournament_participants = $this->tournamentRepository
                    ->select(['participants_count'])
                    ->where('id', $tournament)
                    ->getRow();

                // return $participant; 

                $participated = $this->participantRepository->create([
                    "member_id" => $participant->id,
                    "name" => $participant->name,
                    "gamertag" => $participant->gamertag,
                    "email" => $participant->email,
                    "event_id" => $tournament,
                    "lan_id" => $event
                ]);

                $this->tournamentRepository->update([
                    "participants_count" => $tournament_participants->participants_count + 1
                ], $tournament);

                if( !$participated ) {
                    return $this->api->conflict('Noget gik galt, kunne ikke deltage i turneringen');
                }

                return $this->api->created("Du er nu tilmeldt turneringen");
            }
        }
    }