<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\TrainingRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;

    if( !class_exists('TrainingService') )
    {
        class TrainingService implements ServiceInterface
        {
            /**
             * Training Repository
             *
             * @var DxlEvents\Classes\Repositories\TrainingRepository
             */
            public $trainingRepository;

            /**
             * Participant Repository
             *
             * @var DxlEvents\Classes\Repositories\ParticipantRepository
             */
            public $participantRepository;

            /**
             * Training service constructor
             */
            public function __construct()
            {
                $this->trainingRepository = new TrainingRepository();
                $this->participantRepository = new ParticipantRepository();
            }

            /**
             * Get event ressource from specific event identifier
             *
             * @param int $event
             * @return void
             */
            public function getEvent($event)
            {
                return $this->trainingRepository->find($event) ?? false;
            }

            /**
             * participate event with required member data
             *
             * @param object $event
             * @param object $member
             * @return boolean
             */
            public function participate(object $member, object $event)
            {
                $participant = $this->participantRepository
                    ->select()
                    ->where('member_id', $member->id)
                    ->whereAnd('event_id', $event->id)
                    ->getRow();
                
                if( $participant ) {
                    return true;
                }

                $this->participantRepository->create([
                    "member_id" => $member->id,
                    "name" => $member->name,
                    "gamertag" => $member->gamertag,
                    "email" => $member->email,
                    "event_id" => $event->id,
                    "lan_id" => 0,
                    "is_training" => 1,
                    "is_cooperation" => 0,
                ]);

                $this->trainingRepository->update([
                    "participants_count" => $event->participants_count + 1
                ], $event->id);
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
                $participant = $this->participantRepository
                    ->select()
                    ->where('member_id', $member)
                    ->whereAnd('event_id', $event->id)
                    ->getRow();

                if( !$participant ) return false;

                $this->participantRepository->setPrimaryIdentifier("member_id");
                $this->participantRepository->removeFromEvent($participant->member_id, $event->id);

                return $this->trainingRepository->update([
                    "participants_count" => $event->participants_count - 1
                ], $event->id);
            }
        }
    }
?>