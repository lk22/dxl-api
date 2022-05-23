<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\TrainingRepository;
    use DxlEvents\Classes\Repositories\ParticipantRepository;

    if( !class_exists('TrainingService') )
    {
        class TrainingService implements ServiceInterface
        {
            protected $test = "test";

            public $trainingRepository;

            public $participantRepository;

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
             * @param int $event
             * @param int $member
             * @return boolean
             */
            public function participate(object $member, $event): bool
            {
                $participant = $this->trainingRepository->participant($member->id);

                if( $participant ) {
                    return false;
                }
                
                $this->trainingRepository->addParticipant([
                    "member_id" => $member->id,
                    "name" => $member->name,
                    "gamertag" => $member->gamertag,
                    "email" => $member->email,
                    "event_id" => $event->id,
                    "lan_id" => 0,
                    "is_training" => 1,
                    "is_cooperation" => 0
                ]);

                return $this->trainingRepository->update([
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
                $participant = $this->participantRepository->select()->where('member_id', $member)->whereAnd('event_id', $event)->getRow();

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