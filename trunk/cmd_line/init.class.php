<?php

class PobsInit {

    public function __construct() {

        $this->set_time_limit();
        // $this->check_sapi_name();
        $this->check_version();
        $this->is_not_posix();
        // $this->check_short_open_tag();
        $this->handle_output_buffering();

        error_reporting(E_ALL | E_STRICT);

        ini_set('track_errors', TRUE);
        ini_set('html_errors', FALSE);
        ini_set('magic_quotes_runtime', FALSE);
        ini_set('allow_call_time_pass_reference', 0);        

    }

    private function set_time_limit() {

        if (get_cfg_var('safe_mode') === '1') {

            trigger_error('SafeMode is on. Can not set timeout.', E_USER_WARNING);

        } else {

            set_time_limit(20);

        }

    }

    private function check_sapi_name() {

        if (php_sapi_name() == 'cgi') {

            die('Unsupported SAPI - please use the CLI binary.');

        }

    }

    private function check_short_open_tag() {

        // ini_get('short_open_tag');

    }

    private function handle_output_buffering() {

        while (@ob_end_flush());
        ob_implicit_flush(1);

    }

    private function is_not_posix() {

        if (!function_exists('fnmatch')) {

            echo <<<EOD

This application does not run on non-POSIX compliant system. 
Typically, this means that you are running this application on a Windows machine.
This application makes heavy use of the fnmatch() function which is available only on POSIX compliant systems.
We suggest you run this application on a POSIX compliant machine such as GNU/Linux, which is available for free download from ...

EOD;

            exit;

        }

    }

    private function check_version() {

        // won't let anything <= 5.0.0
        if (!version_compare(phpversion(), '5.0.1', '>=')) {

            trigger_error(
                          'Version ' . phpversion() . ' is unsupported. You may experience unexpected behaviour.
Please upgrade to the latest PHP release', 
                          E_USER_WARNING

                          );

        }

    }

}
