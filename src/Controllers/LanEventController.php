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
            protected $lanRepository;
            protected $tournamentRepository;
            protected $ApiService;

            public function __construct() 
            {
                $lanRepository = new LanRepository();
                $tournamentRepository = new TournamentRepository();
                $apiService = new ApiService();
            }

            public function tournament(\WP_REST_Request $request) 
            {
                $eventTournament = $request->get_params()["tournament"];
                $event = $request->get_params()["event"];

                $tournament = $this->tournamentRepository->
                    ->select()
                    ->where('id', $eventTournament)
                    ->whereAnd('has_lan', 1)
                    ->whereAnd('lan_id', $event)
                    ->getRow();

                return $this->ApiService->success($tournament);
            }
        }
    }