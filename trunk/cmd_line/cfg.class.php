<?php

// $Id$

class ConfigHandler {

    private $conf_file;

    private $SourceDir;
    private $TargetDir;

    public function __construct($conf_file) {

        $this->conf_file  = $conf_file;
        include_once $this->conf_file;

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

#        $this->UdExcDirArray[] = 'SourceDir';
#        $this->UdExcDirArray[] = 'TargetDir';

        foreach (get_defined_functions() as $val) {
            $this->StdExcFuncArray[] = $val;
        }

        foreach (get_declared_classes() as $class) {
            foreach (get_class_methods($class) as $val) {
                $this->StdExcFuncArray[] = $val;
            }
        }

        $this->StripWhiteSpace = (bool) $this->StripWhiteSpace;

    }

    public function set_source_dir($source_dir) {

        $this->SourceDir = chop($source_dir, '/');
        $this->TargetDir = $this->SourceDir . '_pobs';

    }

}

?>