<?php 
    namespace DxlApi\Controllers;

    use DxlApi\Abstracts\AbstractController as Controller;

    /**
     * Repositories
     */
    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TournamentRepository;

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
             * @var DxlEvents\Classes\Repositories
             */
            protected $tournamentRepository;

            /**
             * Api service
             *
             * @var DxlApi\Services\ApiService
             */
            protected $ApiService;

            /**
             * Constructor
             */
            public function __construct() 
            {
                $lanRepository = new LanRepository();
                $tournamentRepository = new TournamentRepository();
                $apiService = new ApiService();
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

                $tournament = $this->tournamentRepository   
                    ->select()
                    ->where('id', $eventTournament)
                    ->whereAnd('has_lan', 1)
                    ->whereAnd('lan_id', $event)
                    ->getRow();

                return $this->ApiService->success($tournament ?? []);
            }
        }
    }