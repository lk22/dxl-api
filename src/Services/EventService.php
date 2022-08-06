<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TrainingRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;

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
             * Event service constructor
             */
            public function __construct()
            {
                $this->lanRepository = new LanRepository();
                $this->trainingRepository = new TrainingRepository();
                $this->tournamentRepository = new TournamentRepository();
            }

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

            /**
             * Get existing participant for specific event
             * TODO: query needs to be called through reository (#refactoring)
             * @param [type] $event
             * @param [type] $gamertag
             * @return void
             */
            public function getExistingParticipant($event, $gamertag) {
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
        }
    }
?>