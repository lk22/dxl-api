<?php

namespace DxlApi\Utilities;

if( ! class_exists('LoggerUtility') )
{
    class LoggerUtility 
    {
        /**
         * Logger instance
         *
         * @var [type]
         */
        public static $instance = null;

        /**
         * Log message
         *
         * @var [type]
         */
        public static $message;

        /**
         * Path to logfile
         */
        const LOGFILE = ABSPATH . "wp-content/plugins/dxl-api/api.log";

        /**
         * Logger constructor
         */
        public function __construct() 
        {
            if( self::$instance ) {
                return new LoggerUtility();
            }

            return self::$instance;
        }

        /**
         * Log to api log file
         *
         * @param string $message
         * @param integer $level
         * @return void
         */
        public static function log($message, $data = []) 
        {
            self::$message = date("d-m-Y H:i:s", time()) . " (" . $_SERVER['REQUEST_METHOD'] . ")" . $message;

            // append data to log
            if( $data )
            {
                self::$message .= " " . json_encode($data);
            }
            
            if ( is_file(self::LOGFILE) && file_exists(self::LOGFILE) ) {
                return file_put_contents(self::LOGFILE, self::$message, FILE_APPEND);
            }

            touch(self::LOGFILE);
            return file_put_contents(self::LOGFILE, self::$message, FILE_APPEND);
        }
    }
}