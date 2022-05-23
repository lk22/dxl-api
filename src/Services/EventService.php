<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlEvents\Classes\Repositories\LanRepository;
    use DxlEvents\Classes\Repositories\TrainingRepository;

    if( !class_exists('EventService') )
    {
        class EventService implements ServiceInterface
        {
            protected $test = "test";

            public $lanRepository;
            public $trainingRepository;

            public function __construct()
            {
                $this->lanRepository = new LanRepository();
                $this->trainingRepository = new TrainingRepository();
            }
        }
    }
?>