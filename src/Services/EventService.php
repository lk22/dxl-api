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

            public $trainingRepository;
            public $tournamentRepository;

            /**
             * Event service constructor
             */
            public function __construct()
            {
                $this->lanRepository = new LanRepository();
                $this->trainingRepository = new TrainingRepository();
                $this->tournamentRepository = new TournamentRepository();
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

            /**
             * return events only from wished filters
             *
             * @param [type] $filters
             * @return void
             */
            public function getFilteredEvents($filters)
            {
                $events = [];

                foreach($filters as $filter) {
                    if( isset($filter["type"]) && $filter["type"] == "lan" ) {
                        $events["lan"] = $this->lanRepository->select()->where('is_draft', 0)->get();   
                    }
                    if( isset($filter["type"]) && $filter["type"] == "training" ) {
                        $events["training"] = $this->trainingRepository->select()->where('is_draft', 0);
                    }
                    if( isset($filter["type"]) && $filter["type"] == "tournaments" ) {
                        $events["tournaments"] = $this->tournamentRepository->select()->where('is_draft', 0)->get();
                    }
                    
                    // inject url to each event object
                    foreach($events[$filter] as $e => $event) {
                        $events[$filter["type"]][$e]["url"] = "?action=details&type=" . $filter["type"] . "&event=" . $event->id;
                    }
                }

                return $events;
            }
        }
    }
?>