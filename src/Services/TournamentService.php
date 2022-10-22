<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;

    if( !class_exists('TournamentService') )
    {
        class TournamentService implements ServiceInterface
        {
            /**
             * Tournament Repository
             *
             * @var DxlEvents\Classes\Repositories\TournamentRepository
             */
            public $tournamentRepository;

            /**
             * Participant Repository
             *
             * @var DxlEvents\Classes\Repositories\ParticipantRepository
             */
            public $participantRepository;

            /**
             * Tournament service constructor
             */
            public function __construct()
            {
                $this->participantRepository = new ParticipantRepository();
                $this->tournamentRepository = new TournamentRepository();
            }

            /**
             * Get event ressource from specific event identifier
             *
             * @param int $event
             * @return void
             */
            public function getEvent($event)
            {
                return $this->tournamentRepository->find($event) ?? false;
            }

            /**
             * Creating new tournament bound to profile
             *
             * @param array $event
             * @return void
             */
            public function createEvent($event): int
            {
                return $this->tournamentRepository->create($event);
            }

            /**
             * participate event with required member data
             *
             * @param int $event
             * @param int $member
             * @return boolean
             */
            public function participate(object $member, $event)
            {
                $participant = $this->tournamentRepository
                    ->participants()
                    ->select()
                    ->where('member_id', $member->id)
                    ->whereAnd('event_id', $event->id)
                    ->getRow();

                if( $participant ) {
                    return true;
                }
                
                $this->tournamentRepository->participants()->create([
                    "member_id" => $member->id,
                    "name" => $member->name,
                    "gamertag" => $member->gamertag,
                    "email" => $member->email,
                    "event_id" => $event->id,
                    "lan_id" => 0,
                    "is_training" => 0,
                    "is_cooperation" => 0
                ]);

                $this->tournamentRepository->update(["participants_count" => $event->participants + 1], $event->id);

                return false;
            }

            /**
             * Unparticipating event
             *
             * @param [type] $event
             * @param [type] $member
             * @return boolean
             */
            public function unparticipate($event, int $member)
            {
                $participant = $this->participantRepository->select()
                    ->where('member_id', $member)
                    ->whereAnd('event_id', $event->id)
                    ->getRow();

                if( !$participant ) return false;

                $this->participantRepository->setPrimaryIdentifier("member_id");
                $this->participantRepository->delete($participant->member_id, [
                    "event_id" => $event->id
                ]);
                // $this->participantRepository->removeFromEvent($participant->member_id, $event->id);

                return $this->tournamentRepository->update([
                    "participants_count" => $event->participants_count - 1
                ], $event->id) ?? false;
            }
        }
    }
?>