<?php

// Manages all command line options
// Is a wrapper around PEAR::Console_Getopt

// $Id$

class ConsoleOptionsHandler {

    private $short_opts = 'hvV';
    private $long_opts  = array('verbose', 'strip-comments', 'help', 'usage', 'version', 'dry-run');

    private $opts       = array();
    private $opts_cache = array();

    private $usage_file = 'usage.txt';

    public function __construct() {

        require_once 'Console/Getopt.php';

        define ('NO_ARGS', 1);
        define ('INVALID_OPTION', 2);

        $args = Console_Getopt::readPHPArgv();

        if (count($args) <= 1) {

            $this->usage(true);
            exit(1);
           
        }

        if (PEAR::isError($args)) {

            fputs(STDERR, $args->getMessage() . "\n");
            exit (NO_ARGS);

        }

        if ($_SERVER['argv'][0] == $_SERVER['SCRIPT_NAME']) {

            $this->opts = Console_Getopt::getOpt($args, $this->short_opts, $this->long_opts);

        } else {

            $this->opts = Console_Getopt::getOpt2($args, $this->short_opts, $this->long_opts);

        }

        // Are the passed options valid?
        if (PEAR::isError($this->opts)) {

            fputs(STDERR, $this->opts->getMessage() . "\n");
            exit (INVALID_OPTION);

        }

        $this->set_cache();

    }

    private function set_cache() {

        $this->opts_cache['help']           = false; // bool
        $this->opts_cache['version']        = false; // bool
        $this->opts_cache['dry_run']        = false; // bool
        $this->opts_cache['is_verbose']     = false; // bool
        $this->opts_cache['source_dir']     = array();  // array
        $this->opts_cache['strip_comments'] = false; // bool

        $this->opts_cache['help'] = $this->in_opts(array('--help', '--usage', 'h'));
        $this->opts_cache['version'] = $this->in_opts(array('--version', 'V'));
        $this->opts_cache['dry_run'] = $this->in_opts('--dry-run');
        $this->opts_cache['is_verbose'] = $this->in_opts(array('--verbose', 'v'));
        $this->opts_cache['source_dir'] = $this->opts[1];
        $this->opts_cache['strip_comments'] = $this->in_opts('--strip-comments');

        $this->spit_needed();

    }

    private function spit_needed() {

        if ($this->get('help')) {

            $this->usage();

        } elseif ($this->get('version')) {

            // echo version details
            exit;

        } elseif (!$this->get('source_dir')) {

            $this->usage(true);

        }

    }

    public function get($opt) {

        return isset($this->opts_cache[$opt]) ? $this->opts_cache[$opt] : false;

    }

    private function in_opts($needles) {

        foreach((array) $needles as $needle) {

            if ($this->in_array_recursive($needle, $this->opts[0])) {
                return TRUE;
            }

        }

        return FALSE;            

    }

    private function in_array_recursive($needle, &$haystack) {

        if (in_array($needle, $haystack)) {
            return TRUE;
        }

        foreach($haystack as $val) {

            if (is_array($val)) {

                if ($this->in_array_recursive($needle, $val)) {

                    return TRUE;

                }

            }

        }

        return FALSE;

    }

    private function usage($only_proto = false) {
        
        if (!$only_proto) {

            readfile($this->usage_file);

        } else {

            fputs(STDOUT, implode(array_slice(file($this->usage_file), 12, 2)));

        }

        exit;

    }

}

?>