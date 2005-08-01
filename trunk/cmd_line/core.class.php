<?php

/*
   +--------------------------------------------------------------------+
   | POBS - The PHP Obfuscator                                          |
   +--------------------------------------------------------------------+
   | Copyright (c) 2004 Rajesh Kumar                                    |
   +--------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or      |
   | modify it under the terms of the GNU General Public License as     |
   | published by the Free Software Foundation; either version 2 of the |
   | License, or (at your option) any later version.                    |
   |                                                                    |
   | This program is distributed in the hope that it will be useful,    |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of     |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the file  |
   | COPYING included with this source for more details.                |
   +--------------------------------------------------------------------+
   | August 20 2004                                                     |
   | Version 1.00                                                       |
   +--------------------------------------------------------------------+
   | AUTHOR: Rajesh Kumar <rks@meetrajesh.com>                          |
   +--------------------------------------------------------------------+
  
   $Id$

*/

final class PobsCore {

    private $opt;
    private $cfg;

    private $tree_hier;
    private $buf;
    private $file_sep = '*@';
    private $time_start;

    private $var_list = array();
    private $constructs = array();
    
    public function __construct(ConfigHandler $cfg, ConsoleOptionsHandler $opt, $time_start) {

        $this->opt = $opt;
        $this->cfg = $cfg;
        $this->time_start = $time_start;

    }

    public function create_target_dir() {

        $target_dir = $this->cfg->get('TargetDir');

#       //########## COMMENT!! #### // uncomment only when testing
#        `chmod -R 777 $target_dir 2>&1 > /dev/null`;
#       `rm -rf $target_dir`;
#       //########## COMMENT!! ####

        if (file_exists($target_dir)) {

            $this->err('ERROR: Directory ' . $target_dir . ' already exists!');
            return FALSE;

        }

        /* -     if (!@mkdir($target_dir)) { */
        /* + */ 
        if (!mkdir($target_dir)) {

            $this->err('ERROR: Unable to create target directory. Permission denied.');
            return FALSE;

        }

        return TRUE;

    }

    public function gen_long_file($dir = '') {

        $dir = empty($dir) ? $this->cfg->get('SourceDir') : $dir;
        $files = scandir($dir);
        $this->strip_dots($files);

        $lamb = $this->cfg->get('StripWhiteSpace') ? 'php_strip_whitespace' : 'file_get_contents';

        foreach ($files as $file) {

            $copy_file_asis = false;
            $file = $dir . '/' . $file;

            if ($this->is_exc_file($file) && (FALSE === $copy_file_asis = $this->copy_file_asis($file))) {
                continue;
            }

            if (is_dir($file)) {

                if (in_array($this->get_path($file), $this->cfg->get('UdExcDirCopyArray'))) {

                    $this->copy_dir_recursive($file);

                } else {

                    $this->out('Scanning ' . $file);
                    $this->gen_long_file($file);

                }

            } elseif (is_file($file)) {

                if (count($this->tree_hier) >= $this->cfg->get('MaxFiles')) {

                    $this->err("\n" . 'STOPPING SCAN. MaxFiles Limit reached.');
                    break;

                }

                $this->tree_hier[] = $file;

                if (TRUE === $copy_file_asis) {

                    $this->out('Copying ' . $file);
                    $this->copy_file($file);
                    
                } else {

#                    $this->out('Inspecting ' . $file);
                    $this->buf .= $this->get_file_sep($file) . $lamb($file);

                }

            } elseif (is_link($file)) {

                // do somethin here
                // preferably copy as is

                $this->out($file . 'is a symbolic link');

            } else {

                $this->out('Ignoring ' . $file . ': Unknown file type');

            }

        }

    }

    private function is_exc_file($file) {

        if (!is_readable($file)) {
            return true;
        }

        $tmp = $this->get_path($file);

        if (is_dir($file)) {

            return in_array($tmp, $this->cfg->get('UdExcDirArray')) && !in_array($tmp, $this->cfg->get('UdExcDirCopyArray'));

        } elseif (is_file($file)) {

            return in_array($tmp, $this->cfg->get('UdExcFileArray')) || $this->is_bad_ext($file);

        }

        return false;

    }

    private function get_file_sep($file) {
        
        $tmp = str_repeat($this->file_sep, 12) . substr($this->file_sep, 0, 1);
        return "\n\n" . $tmp . "\n" . $file . "\n" . $tmp . "\n\n";
        // return "\n\n" . $tmp . $file . $tmp . "\n\n";

    }        
    
    private function is_bad_ext($file) {

        return !$this->my_fnmatch($this->cfg->get('FileExtArray'), $file);
        // return '.' . array_pop(explode('.', $file));

    }

    private function my_fnmatch($pats, $filename) {

#        echo '"' . $filename . "\n";

        foreach ((array) $pats as $pat) {
            
            if (fnmatch($pat, $filename)) {
                return TRUE;
            }

        }

        return FALSE;

    }

    private function copy_file_asis($file) {

#        echo '+' . $this->get_path($file) . "\n";

        return $this->my_fnmatch($this->cfg->get('FileExtCopyArray'), $this->get_path($file));

#        var_dump($foo);
#        return $foo;

    } /*  ----o
       *      |
       *      |
       *      |
       *     \_/  */
    private function copy_file($file) { // copying file as is

        $target_file = $this->get_target_from_source($file);
        $target_dir  = dirname($target_file);

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        copy($file, $target_file);

    }

    public function buf_get_len() {

        return strlen($this->buf);
    }

    public function buf_dump() {

        fputs(STDOUT, $this->buf);
    }

    private function filter_small_values($val) {

        return strlen($val) > 3;
    }

    private function filter_method_calls($val) {

        return !(strlen($val) == ($this->cfg->get('MD5KeyLen') + 1)  && substr($val, 0, 1) == 'F');
    }

    private function sort_length($a, $b) {

        return strlen($a) < strlen($b);

    }
    
    public function replace_with_hashes() {

        if ($this->opt->get('dry_run')) {
            return;
        }

        if (!$this->cfg->get('StripWhiteSpace') && $this->opt->get('strip_comments')) {
            $this->strip_comments();
        }

        /**
         * Match function definitions
         * PREG_PATTER_ORDER is unneeded
         * '("\\<\\(function\\)\\s-+&?\\(\\(?:\\sw\\|\\s_\\)+\\)\\s-*("
         */

        $this->out("\n" . 'Scanning for functions...');
        preg_match_all('/function\s+(\w+)\s*\(/i', $this->buf, $catches, PREG_PATTERN_ORDER);
        $this->out('Replacing functions with MD5 keys... Done');

        $catches = array_diff($catches[1], array_merge($this->cfg->get('UdExcFuncArray'), $this->cfg->get('StdExcFuncArray')));
        $catches = array_filter($catches, array($this, 'filter_small_values'));

        foreach ($catches as $catch) {

            $this->buf = preg_replace('/(?<!new\s|_|\B)' . $catch . '\s*\(/', $this->hash($catch, 'func') . '(', $this->buf);
            $this->buf = preg_replace('/new\s+' . $this->hash($catch, 'func') . '\(/', 'new ' . $catch . '(', $this->buf);

        }

        /**
         * Match classes definitions
         * PREG_PATTER_ORDER is unneeded
         * '("\\<\\(class\\|interface\\)[ \t]*\\(\\(?:\\sw\\|\\s_\\)+\\)?"
         * '("\\<\\(new\\|extends\\|implements\\)\\s-+\\$?\\(\\(?:\\sw\\|\\s_\\)+\\)"
         */

        $this->out("\n" . 'Scanning for classes...');
        preg_match_all('/class\s+(\w+)(?:\s+extends\s+(\w+))?\s*\{/i', $this->buf, $catches, PREG_SET_ORDER);

        foreach ($catches as $catch) {

            if (!in_array($catch[1], $this->cfg->get('UdExcClassArray'))) {

                $hash = $this->hash($catch[1], 'class');
                $this->constructs[] = $catch[1];

                $this->buf = preg_replace('/class\s+' . $catch[1] . '(\s+|\{)/i', 'class ' . $hash . '\1', $this->buf);
                $this->buf = preg_replace('/new\s' . $catch[1] . '(\W+)/i', 'new ' . $hash . '\1', $this->buf); // instantiations
                $this->buf = preg_replace('/function\s+' . $this->hash($catch[1], 'func') . '(\b)/', 'function ' . $this->hash($catch[1], 'cnstrtr') . '\1', $this->buf); // constructs
                $this->buf = preg_replace('/' . $catch[1] . '::/i' , $hash . '::', $this->buf);
                $this->buf = preg_replace('/::' . $this->hash($catch[1], 'func') . '\(/', '::' . $this->hash($catch[1], 'class') . '(', $this->buf);

            }
            
            if (isset($catch[2]) && !in_array($catch[2], $this->cfg->get('UdExcClassArray'))) {

                $this->buf = preg_replace('/class\s+(\w{' . ($this->cfg->get('MD5KeyLen') + 1) .'})\s+extends\s+' . $catch[2] . '(\s+|\{)/i',
                                          'class \1 extends ' . $this->hash($catch[2], 'class') . '\2', 
                                          $this->buf);

            }

        }

        $this->out('Replacing classes and instances with MD5 keys... Done');


        /*
         * Match variables
         * PREG_PATTER_ORDER is unneeded
         * '("\\$\\(\\(?:\\sw\\|\\s_\\)+\\)" (1 font-lock-variable-name-face)) ; $variable
         */

        $this->out("\n" . 'Scanning for regular variables...');
        preg_match_all('/\$(\w+)/i', $this->buf, $catches, PREG_PATTERN_ORDER);
        $this->out('Replacing variables with MD5 keys... Done');

        $catches = array_unique($catches[1]);
        $catches = array_diff($catches, array_merge($this->cfg->get('StdExcVarArray'), $this->cfg->get('UdExcVarArray')));
        $catches = array_filter($catches, array($this, 'filter_small_values'));
        usort($catches, array($this, 'sort_length'));

        foreach ($catches as $catch) {

            $this->buf = str_replace('$' . $catch, '$' . $this->hash($catch, 'var'), $this->buf);

        }

        /*
         * Match $this->var_name and $API->var_name style variables
         * PREG_PATTER_ORDER is unneeded
         */

        $this->out("\n" . 'Scanning for class variables...');
        preg_match_all('/\->(\w+)/i', $this->buf, $catches, PREG_PATTERN_ORDER);
        $this->out('Replacing class variables with MD5 keys... Done');

        $catches = array_unique($catches[1]);
        $catches = array_diff($catches, array_merge($this->cfg->get('StdExcVarArray'), $this->cfg->get('UdExcVarArray')));
        $catches = array_filter($catches, array($this, 'filter_small_values'));
        $catches = array_filter($catches, array($this, 'filter_method_calls'));
        usort($catches, array($this, 'sort_length'));

        foreach ($catches as $catch) {

            $this->buf = preg_replace('/->' . preg_quote($catch) . '(?!\()/', '->' . $this->hash($catch, 'var'), $this->buf);
        }

    }

    private function hash($string, $type) {

        switch ($type) {

        case 'func':
            $c = 'F';
            break;
        case 'class':
            $c = 'C';
            break;
        case 'cnstrtr':
            $c = 'C';
            break;
        case 'var':
            $c = 'V';
            break;
        default:
            $c = 'P';
            break;

        }

        return $c . substr(md5($string), 1, $this->cfg->get('MD5KeyLen'));

    }
    

    public function write_target_files() {

        $this->out("\n" . 'Scan Complete. Preparing to write to target...');
        $this->out('Changing directory to ' . $this->cfg->get('TargetDir') . "/\n");
        chdir($this->cfg->get('TargetDir'));

        $pattern = preg_quote('#' . $this->get_file_sep('-0-') . '#i');
        $pattern = str_replace('-0-', '(.*)', $pattern);

        $files = preg_split($pattern, $this->buf, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $alias =& $files;

        foreach ($alias as $file) {

            $file = $this->get_path($file);
            $dirname = dirname($file);

            if ($dirname != '.' && !is_dir($dirname)) {

                $this->out('Creating folder ' . $this->cfg->get('TargetDir') . '/' . $dirname);
                mkdir($dirname);

            }

            $data = current($alias);
            next($alias);

#            $this->out('Writing obfuscated code to ' . $this->cfg->get('TargetDir') . '/' . $file);
            file_put_contents($file, $data);

        }

        require_once 'Console/Color.php';

        $this->out('');
        $this->out(count($this->tree_hier) . ' file(s) obfuscated with no errors.');
        $this->out(round($this->buf_get_len() / 1024) . ' KB of output written to ' . Console_Color::convert('%9' . $this->cfg->get('TargetDir') . '%n') . '/');
        $this->out('Please execute scripts to ensure they work as intended.');

    }

    private function get_path($file) {

        return str_replace($this->cfg->get('SourceDir') . '/', '', $file);

    }

    private function get_target_from_source($target) {

        // str_replace() doesn't impose a limit, preg_replace() does
        return preg_replace('#^' . $this->cfg->get('SourceDir') . '/#', $this->cfg->get('TargetDir') . '/', $target, 1);

    }

    private function copy_dir_recursive($dir) {

        $this->out('Copying ' . $dir);

        $target_dir = dirname($this->get_target_from_source($dir));
                    
        if (!is_dir($target_dir)) {

            mkdir($target_dir, 0755, true);

        }

        exec('cp -RPpd ' . $dir . ' ' . $target_dir);

        //       copy ($file . '/', $this->get_target_from_source($file) . '/');

    }

    private function strip_dots(&$ar) {

        unset($ar[array_search('.', $ar)]);
        unset($ar[array_search('..', $ar)]);

        //        return substr($file, -1, 2) == '.';

    }

    public function set_stage_file() {

        $file_name    = $this->cfg->get('SourceDir');
        $tmp_dirname = 'POBS_' . substr(md5(mt_rand() . time()), 0, 8);

        $this->out('Creating temporary directory ' . $tmp_dirname);        
        mkdir($tmp_dirname);
        copy($file_name, $tmp_dirname . '/' . basename($file_name));
        $this->cfg->set_source_dir($tmp_dirname);

    } /*  ----o
              |
              |
              |
             \_/  */
    public function cleanup_stage_file($file_name) {

        $file_name = basename($file_name);

        if (file_exists($file_name . '_pobs')) {

            $this->err('ERROR: File ' . $file_name . '_pobs already exists! No output written.');

        } elseif (!$this->opt->get('dry_run')) {

            copy($this->cfg->get('TargetDir') . '/' . $file_name, './' . $file_name . '_pobs');
            $this->out("\n" . 'Output written to ' . $file_name . '_pobs');

        }

        unlink($this->cfg->get('SourceDir') . '/' . $file_name);
        unlink($this->cfg->get('TargetDir') . '/' . $file_name);

        rmdir($this->cfg->get('SourceDir'));
        rmdir($this->cfg->get('TargetDir'));

    }

    private function out($st) {

        if ($this->opt->get('is_verbose')) {
            fputs(STDOUT, $st . "\n");
        }

    }

    private function err($st) {

        // ncurses_beep()
        fputs(STDERR, $st . "\n");

    }

    public function time_elapsed() {

        if (is_object($this->opt)) {
            $this->out("\n" . 'Elapsed Time: ' . round(microtime(true) - $this->time_start, 2) . ' seconds');
        }

    }


}

/* SHANTIH! */

?>