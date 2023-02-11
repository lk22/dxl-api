<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\TournamentSettingRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;
    use DxlEvents\Classes\Repositories\LanParticipantRepository;
    use DxlMembership\Classes\Repositories\MemberRepository;
    use DxlEvents\Classes\Repositories\GameRepository;
    use DxlEvents\Classes\Repositories\GameTypeRepository;
    use DxlEvents\Classes\Repositories\GameModeRepository;

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
    use DxlEvents\Classes\Mails\LanEventUnparticipated;
    use DxlEvents\Classes\Mails\EventUnparticipated;
    use DxlEvents\Classes\Mails\LanEventFoodOrderUpdate;

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

            protected $TournamentSettingRepository;

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

            protected $gameRepository;
            protected $gameTypeRepository;
            protected $gameModeRepository;

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
                $this->TournamentSettingRepository = new TournamentSettingRepository();
                $this->participantRepository = new ParticipantRepository();
                $this->lanParticipantRepository = new LanParticipantRepository();
                $this->gameRepository = new GameRepository();
                $this->gameTypeRepository = new GameTypeRepository();
                $this->gameModeRepository = new GameModeRepository();
                $this->memberRepository = new MemberRepository();
                $this->api = new ApiService();
                $this->eventService = new EventService();
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
                $participant = $request->get_params()["participant"] ?? 0;
                // return $event;

                $participantMember = $this->memberRepository->find($participant);

                $tournament = $this->tournamentRepository   
                    ->select()
                    ->where('id', $eventTournament)
                    ->whereAnd('has_lan', 1)
                    ->whereAnd('lan_id', $event)
                    ->getRow();

                $settings = $this->TournamentSettingRepository->find($tournament->id);
                $game = $this->gameRepository->find($settings->game_id);
                $gameType = $this->gameTypeRepository->find($game->game_type);
                $gameMode = $this->gameModeRepository->find($settings->game_mode);

                $participants = $this->participantRepository->findByEvent($tournament->id);
                
                $participated = $this->participantRepository->
                    select()
                    ->where('event_id', $tournament->id)
                    ->whereAnd('member_id', $participant)
                    ->getRow();

                $participants_data = [];
                
                foreach ( $participants as $p => $participant ) {
                    $member = $this->memberRepository->find($participant->member_id);
                    $participants_data[$p] = [
                        "id" => $member->id,
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
                    "participants" => $participants_data,
                    "participated" => ($participated !== null) ? true : false,
                    "member" => $participantMember,
                    "game" => $game->name ?? "Ikke angivet",
                    "game_type" => $gameType->name ?? "Ikke angivet",
                    "game_mode" => $gameMode->name ?? "Ikke angivet",
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
                $eventService = new EventService();
                $memberService = new MemberService();
                
                $seatedMembers = $request->get_param('members') ?? [];
                $has_companion = $request->get_param('companion_checked');
                $companion = $request->get_param('companion_data');
                $breakfast = $request->get_param("breakfast");
                $dinner_friday = $request->get_param("dinner_friday");
                $dinner_saturday = $request->get_param("dinner_saturday");

                $participantExists = $eventService->getExistingParticipant(
                    $request->get_param("event"), 
                    $request->get_param("gamertag")
                );

                $lanEvent = $this->lanRepository->find($request->get_param('event'));

                $eventTermsAccepted = $request->get_param('event_terms_accepted');

                if( $participantExists ) {
                    return $this->api->conflict("Du er allerede tilmeldt denne begivenhed");
                }

                if ( isset($companion) && ! $eventService->validateCompanion($companion) ) {
                    return $this->api->conflict("Ledsager oplysninger er ikke korrekt udfyldte");
                }

                $gamertag = $request->get_param("gamertag");

                $member = $this->memberRepository->select()->where('gamertag', "'$gamertag'")->getRow();

                $participant = $this->lanParticipantRepository->create([
                    "event_id" => $request->get_param('event'),
                    "member_id" => $member->id,
                    "has_companion" => $has_companion,
                    "name" => $member->name,
                    "gamertag" => $member->gamertag,
                    "has_saturday_breakfast" => $breakfast,
                    "has_saturday_breakfast" => $breakfast,
                    "has_sunday_breakfast" => $breakfast,
                    "has_sunday_breakfast" => $breakfast,
                    "has_friday_lunch" => $dinner_friday,
                    "has_saturday_dinner" => $dinner_saturday,
                    "participated" => time(),
                    "event_terms_accepted" => $eventTermsAccepted,
                    "seat_companions" => json_encode($seatedMembers),
                ]);
                
                if( !$participant ) {
                    return $this->api->conflict("Der skete en fejl, kunne ikke tilmelde dig begivenheden");
                }
                
                $this->lanRepository->update([
                    "participants_count" => $lanEvent->participants_count + 1,
                ], $lanEvent->id);

                $seats_updated = $eventService->removeAvailableSeat($request->get_param("event"));

                $participantMail = (new EventParticipatedMail(
                    $member, 
                    $lanEvent, 
                    $seatedMembers, 
                    $request->get_param('message'),
                    $companion,
                ))->setSubject('Tilmelding ' . $member->gamertag . ')')
                    ->setReciever($member->email)
                    ->send();

                $participantNotification = (new LanEventParticipatedMail(
                    $member, 
                    $lanEvent, 
                    $seatedMembers, 
                    $request->get_param('message'),
                    $companion,
                    $eventTermsAccepted
                ))->setSubject("Ny tilmelding, " . $member->gamertag)
                    ->setReciever("medlemskab@danishxboxleague.dk")
                    ->send();

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
                global $wpdb;
                $eventId    = $request->get_param('event');
                $memberId   = $request->get_param('member');
                $message    = $request->get_param('messageValue') ?? "";
                $eventService = new EventService();

                $member = $this->memberRepository
                    ->select()
                    ->where('id', $memberId)
                    ->getRow();

                $event = $this->lanRepository->find($eventId);
                
                $tournaments = $this->tournamentRepository
                    ->select()
                    ->where('lan_id', $eventId)
                    ->get();

                foreach($tournaments as $tournament) {
                    $pcount = $this->tournamentRepository
                        ->select(['participants_count'])
                        ->where('id', $tournament->id)
                        ->getRow();

                    $participant = $this->participantRepository
                        ->select()
                        ->where('member_id', $member->id)
                        ->whereAnd('event_id', $tournament->id)
                        ->getRow();

                    // if the participant exists on the tournament, remove the participant
                    if( $participant ) {
                        $wpdb->delete(
                            $wpdb->prefix . 'event_participants',
                            ['id' => $participant->id]
                        );

                        // if( $removed ) {
                            $this->tournamentRepository->update(
                                ['participants_count' => $pcount->participants_count - 1],
                                $tournament->id
                            );
                        // }
                    }
                }

                $participant = $this->lanParticipantRepository
                    ->select()
                    ->where('member_id', $member->id)
                    ->whereAnd('event_id', $eventId)
                    ->getRow();
                
                $removed = $this->lanParticipantRepository->removeFromEvent($participant->id, $eventId);

                if ( !$removed ) {
                    return $this->api->conflict("Der skete en fejl, kunne ikke afmelde dig begivenheden");
                }

                $seats_updated = $eventService->addAvailableSeat($request->get_param("event"));

                $this->lanRepository->update([
                    "participants_count" => $event->participants_count - 1,
                ], $event->id);

                // notify event manager
                $unparticipatedNotification = (new LanEventUnparticipated($event, $member, $message))
                    ->setSubject('Afmelding, ' . $member->name)
                    ->setReciever('medlemskab@danishxboxleague.dk')
                    ->send();

                // notify the participant that the unparticipation is recieved
                $unparticipatedNotification = (new EventUnparticipated($event, $member))
                    ->setSubject('Afmelding, ' . $event->title)
                    ->setReciever($member->email)
                    ->send();

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

                $participant = $this->memberRepository->find($member);
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

            /**
             * Unparticipate tournament resource
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function unparticipateTournament(\WP_REST_Request $request) {
                global $wpdb;

                $TID = $request->get_param('tournament');
                $event = $request->get_param('event');
                $member = $request->get_param('participant');

                $participant = $this->memberRepository->find($member);
                $tournament = $this->tournamentRepository->find($TID);

                $participant = $this->participantRepository
                    ->select()
                    ->where('member_id', $member)
                    ->whereAnd('event_id', $tournament->id)
                    ->getRow();

                if ( !$participant ) {
                    return $this->api->conflict('Du er ikke tilmeldt turneringen');
                }

                /**
                 * TODO: refactor to use repository
                 */
                $deleted = $wpdb->delete(
                    $wpdb->prefix . 'event_participants',
                    ['id' => $participant->id],
                    ['%d']
                );

                // $deleted = $this->participantRepository->delete($participant->id);

                if ( ! $deleted ) {
                    return $this->api->conflict('Noget gik galt, kunne ikke afmelde dig turneringen');
                }

                $this->tournamentRepository->update([
                    "participants_count" => $tournament->participants_count - 1
                ], $tournament->id);

                return $this->api->success('Du er nu afmeldt turneringen');
            }

            /**
             * updating food ordering
             *
             * @param \WP_REST_Request $request
             * @return void
             */
            public function updateFoodOrder(\WP_REST_Request $request) 
            {
                global $wpdb;
                $participantID = $request->get_param('participant');
                $foodOrder = $request->get_param('foodOrder');
                $note = $request->get_param('foodOrderNote');

                $member = $this->memberRepository->find($participantID);

                $updatedFoodOrder = $wpdb->update($wpdb->prefix . "lan_participants", [
                    "food_ordered" => 1,
                    "has_friday_breakfast" => (isset($foodOrder["has_friday_breakfast"])) ? 1 : 0,
                    "has_saturday_breakfast" => (isset($foodOrder["has_saturday_breakfast"])) ? 1 : 0,
                    "has_saturday_lunch" => (isset($foodOrder["has_saturday_lunch"])) ? 1 : 0,
                    "has_saturday_dinner" => (isset($foodOrder["has_saturday_dinner"])) ? 1 : 0,
                    "has_sunday_breakfast" => (isset($foodOrder["has_sunday_breakfast"])) ? 1 : 0
                ], ["member_id" => $participantID]);
                
                // $updatedFoodOrder = $this->eventService->updateFoodOrderParticipant($foodOrder, intval($participantID));
                if( ! $updatedFoodOrder ) {
                    return $this->api->error([$foodOrder]);
                }

                // send new mail to event handler about participant food order update
                $foodOrderUpdateMail = (new LanEventFoodOrderUpdate($foodOrder, $member, $note))
                    ->setSubject("Lan Deltager " . $member->name . " mad bestilling")
                    ->setReciever('medlemskab@danishxboxleague.dk')
                    ->send();
                    
                return $this->api->success("Dine mad ønsker er nu registreret, du vil modtage en faktura snarest for din mad bestilling");
            }
        }
    }