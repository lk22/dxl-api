<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TrainingRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;
    use DxlEvents\Classes\Repositories\LanParticipantRepository;

    if( !class_exists('EventService') )
    {
        class EventService implements ServiceInterface
        {

            /**
             * Lan Repository
             *
             * @var DxlEvents\Classes\Repositories\LanRepository
             */
            public $lanRepository;

            /**
             * Training Repository
             *
             * @var DxlEvents\Classes\Repositories\TrainingRepository
             */
            public $trainingRepository;

            /**
             * Tournament Repository
             *
             * @var DxlEvents\Classes\Repositories\TournamentRepository
             */
            public $tournamentRepository;

            /**
             * Event service constructor
             */
            public function __construct()
            {
                $this->lanRepository = new LanRepository();
                $this->trainingRepository = new TrainingRepository();
                $this->tournamentRepository = new TournamentRepository();
                $this->lanParticipantRepository = new LanParticipantRepository();
            }

            /**
             * Get all events from member
             *
             * @param [type] $member
             * @return void
             */
            public function fetchAllEventsFromMember($member) {
                $events = [];

                $lans = $this->lanRepository->getLansByMember($member);
                $trainings = $this->trainingRepository->getTrainingsByMember($member);
                $tournaments = $this->tournamentRepository->getByMember($member);

                foreach($lans as $lan) {
                    $events[] = $lan;
                }

                foreach($trainings as $training) {
                    $events[] = $training;
                }

                return $events;
            }

            public function fetchMemberProfileEvents($member) {
                $events = [
                    "training" => [],
                    "tournaments" => [],
                ];

                $trainings = $this->trainingRepository->getTrainingsByMember($member);
                $tournaments = $this->tournamentRepository->getByMember($member);

                foreach($tournaments as $tournament) {
                    $events["tournaments"] = $lan;
                }

                foreach($trainings as $training) {
                    $events["training"] = $training;
                }

                return $events;
            }

            /**
             * Create new event from specific type
             *
             * @param string $type
             * @param array $event
             * @return void
             */
            public function createEvent(string $type, array $event) {
                switch($type) {
                    case 'training':
                        $training = $this->trainingRepository->create($event);
                        return $training;
                    case 'tournament':
                        $tournament = $this->tournamentRepository->create($event);
                        return $tournament;
                }

                return false;
            }

            /**
             * Undocumented function
             *
             * @param string $type
             * @param array $event
             * @return void
             */
            public function updateEvent(string $type, array $event) {
                switch($type) {
                    case 'training':
                        $tevent = $this->trainingRepository->find($event['id']);
                        $training = $this->trainingRepository->update($event, $tevent->id);
                        return $training;
                    case 'tournament':
                        $tevent = $this->tournamentRepository->find($event['id']);
                        $tournament = $this->tournamentRepository->update($event, $tevent->id);
                        return $tournament;
                }

                return false;
            }
            

            /**
             * Get existing participant for specific event
             * TODO: query needs to be called through reository (#refactoring)
             * @param int $event
             * @param string $gamertag
             * @return void
             */
            public function getExistingParticipant(int $event, string $gamertag) {
                global $wpdb;

                $participantExists = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM " . $wpdb->prefix . "lan_participants WHERE event_id = %d AND gamertag = %s",
                        $event,
                        $gamertag
                    )
                );

                return ($participantExists) ? true : false;
            }

            /**
             * updating available seats on specific LAN event
             *
             * @param [type] $event
             * @return void
             */
            public function removeAvailableSeat($event) {
                $seats = $this->lanRepository->select('seats_available')->where('id', $event)->getRow();
                
                $updated = $this->lanRepository->update(["seats_available" => $seats->seats_available - 1], $event);
                return ($updated == false) ? false : true;
            }

            /**
             * Validating participant companion
             *
             * @param array $companion
             * @return boolean
             */
            public function validateCompanion($companion): bool 
            {
                if( $companion["is_checked"] && empty($companion['name']) ) {
                    return false;
                }

                return true;
            }

            /**
             * Updating food Order on participant
             *
             * @param [type] $foodOrder
             * @param [type] $participant
             * @return void
             */
            public function updateFoodOrderParticipant($foodOrder, $participant) 
            {
                $foodOrderData = [];
                // return $foodOrder;
                $where = [];
                foreach ($foodOrder as $key => $item) {
                    // return in_array($item, $foodOrder);
                    if( ! array_key_exists($item, $foodOrderData) ) {
                        $foodOrderData[$key] = $foodOrder[$key];
                    }
                }

                // return $foodOrderData;

                $updatedFoodOrder = $this->lanParticipantRepository->update($foodOrder, $participant);
                return ( !$updatedFoodOrder ) ? false : true;
            }
        }
    }
?>