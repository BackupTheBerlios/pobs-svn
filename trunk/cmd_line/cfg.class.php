<?php

// $Id$

class ConfigHandler {

    private $conf_file;

    private $SourceDir;
    private $TargetDir;

    public function __construct($conf_file) {

        $this->conf_file  = $conf_file;

        if (!php_check_syntax($this->conf_file, $msg)) {

            fputs(STDERR, 'Syntax errors were detected in ' . $this->conf_file . ":\n\n" . ucfirst($msg) . "\n");
            exit;

        }

        require_once $this->conf_file;

        $ar = get_defined_vars();

        foreach ($ar as $key => $val) {

            $this->$key = $val;

        }

        $this->set_def_conf_vars();

    }

    public function get($var_name) {

        if (isset($this->$var_name)) {

            return $this->$var_name;

        } else {

            trigger_error('$' . $var_name . ' is not a configuration variable', E_USER_ERROR);

        }

    }

    final private function set_def_conf_vars() {

        $this->UdExcDirArray[] = 'SourceDir';
        $this->UdExcDirArray[] = 'TargetDir';

        $this->StripWhiteSpace = (bool) $this->StripWhiteSpace;

    }

    public function set_source_dir($source_dir) {

        $this->SourceDir = chop($source_dir, '/');
        $this->TargetDir = $this->SourceDir . '_pobs';

    }

}

?>