<?php

/*
   +--------------------------------------------------------------------+
   | POBS - The PHP Obfuscator                                          |
   +--------------------------------------------------------------------+
   | Copyright (c) 2004 RKS Development Labs                            |
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
   | AUTHOR: Rajesh Kumar <rajesh@meetrajesh.com>                       |
   +--------------------------------------------------------------------+
  
   $Id$

*/

require_once 'pobs.conf.php';

class PobsCore {

    var $StartTime;
    var $CopyrightText;

    var $TotalFileSizeRead  = 0;
    var $TotalFileSizeWrite = 0;
    var $NewlinesReplaced   = 0;

    var $ExVarArray     = array();
    public $FuncArray      = array();
    var $ClassArray     = array();
    var $ConstArray     = array();
    var $VarArray       = array();
    var $ObjectVarArray = array();
    var $JSVarArray     = array();
    var $JSFuncArray    = array();

    var $UdExcVarArrayWild   = array();
    var $UdExcVarArrayDliw   = array(); 
    var $UdExcFileArrayRegEx = array();
    var $UdExcDirArrayRegEx  = array();

    var $LineArray = array();
    var $FileArray = array();

    var $ExcludedLines = array();

    var $pobs;

    public function __construct($pobs) {

        ini_set('allow_call_time_pass_reference', 0);
        set_magic_quotes_runtime(0);

        if (get_magic_quotes_gpc() === 1) {

            /* array_walk_recursive buggy as of PHP 5.0.1
            array_walk_recursive($_POST, 'stripslashes');*/

        }

        $this->pobs = $pobs;

        $this->StartTime = time();
        $this->CopyrightText = str_replace("\r", '', trim($_POST['CopyrightText']));

        krsort($this->FuncArray);
        krsort($this->ConstArray);
        krsort($this->VarArray);
        sort($this->FileArray);

    }

    public function check_safe_mode() {

        if (get_cfg_var('safe_mode') === '1') {

            trigger_error($this->pobs->safe_mode_warning(), E_USER_WARNING);

        } else {

            global $TimeOut;
            set_time_limit($TimeOut);

        }

    }

    public function get_wild_cards() {

        // Scan UdExcVarArray and move the Variables with Wildcards (*) to a separate array
        // Separating the variables with wildcards speeds up the scanning and checking process

        global $UdExcVarArray;
        global $UdExcFileArray, $UdExcDirArray;
    
        // process Exclude File array
        foreach ($UdExcFileArray as $val) {

            // convert to regular expression
            # $val = str_replace(".", "\.", $val);
            $val = str_replace('*', '.*', $val);
            $val = '/^' . $val . '/i';
            $this->UdExcFileArrayRegEx[] = $val;

        }

        foreach ($UdExcDirArray as $val) {

            // convert to regular expression
            # $val = str_replace('.', '\.', $val);
            #$val = str_replace('\\', '\/', $val);
            #$val = str_replace('\\', '\/', $val);
            #$val = str_replace('/', '\/', $val);
            $val = str_replace('*', '.*', $val);
            $val = '/' . $val . '/i';
            $this->UdExcDirArrayRegEx[] = $val;

        }

        foreach ($UdExcVarArray as $key => $val) {

            // SB adding support for wildcards that are wild at the front end (e.g. "*_x"
            // $pos=strrpos($val, "*");
            // if ($pos!==FALSE) {

            $pos = strrpos(' ' . $val, '*');

            if ($pos > 1) { // true of properly formed standard wildcards (* at end)

                echo 'WildCardValue:' . $val . '<br />';
                array_push($UdExcVarArrayWild, str_replace('*', '', $val));
                $UdExcVarArray[$key] = 'Niets' . $key;

            } elseif ($pos == 1) { // true of backwards wildcards (* at front)

                echo 'DliwCardValue:' . $val . '<br />';
                array_push($UdExcVarArrayDliw, str_replace('*', '', $val));
                $UdExcVarArray[$key] = 'Niets' . $key;

            }

        }

        echo '&nbsp;<br />';

    }

    public function scan_source_files ($path = '') {

        global $ExVarArray, $FuncArray, $ClassArray, $ConstArray, $VarArray, $LineArray, $FileArray;
        global $SourceDir, $TargetDir, $FileExtArray, $JSFileExtArray, $JSFuncArray, $ReplaceJS, $ReplaceFunctions, $ReplaceVariables, $ReplaceConstants, $MaxFiles;
#       global $RecursiveScan, $CopyAllFiles; // File system option...
        global $StdExcJSFuncArray, $UdExcVarArray, $UdExcVarArrayWild, $UdExcFuncArray, $UdExcConstArray;
    
        $dir = dir($SourceDir . $path . '/');

        while ($FileNaam = $dir->read()) {

            if ($FileNaam == '.' || $FileNaam == '..') {
                continue;
            }

            $fileName = $path . '/' . $FileNaam;

            $excludeFile = false;
            $excludeDirectory = false;
        
            if (is_file($SourceDir . $fileName)) {

                // check if file has the proper suffix
                $extpos = strrpos($FileNaam, '.');

                if($extpos > 0) {

                    $Suffix = substr($FileNaam, $extpos + 1);

                } else {

                    $Suffix = md5(rand()); // generate some non existing extension

                }

                if ((in_array($Suffix, $FileExtArray) || ($extpos == 0 && in_array('.', $FileExtArray)) || (in_array($Suffix, $JSFileExtArray) && $ReplaceJS)) 
                    && sizeof($FileArray) < $MaxFiles) {

                    // check if the file is in UdExcFileArray
                    foreach($this->UdExcFileArrayRegEx as $value) {

                        // compare file name with regular expression  
                        if(preg_match($value, $FileNaam)) {

                            $excludeFile = true;
                        }

                    }
                
                    if (false === $excludeFile) {

                        if (in_array($Suffix, $JSFileExtArray)) {

                            // is JavaScript file
                            echo '<b>+ Scanning JavaScript File: ' . substr($fileName, 1) . '</b><br>' . "\n";
                            array_push($FileArray, substr($fileName, 1));
                            $LineArray = file($SourceDir . $fileName);
                            ob_flush();
                    
                            for ($rgl = 0; $rgl < sizeof($LineArray); $rgl++) {
                                
                                $Line = trim(strtolower($LineArray[$rgl]));

                                if (($ReplaceJS) && substr($Line, 0, 9) == 'function ' ) { // Search for Function declaration

                                    // we have to find out if function is JavaScript Function or PHP function
                                    $posEinde = strpos($Line, '(');
                                    $FunctieNaam = substr(trim($LineArray[$rgl]), 0, $posEinde);
                                    $FunctieNaam = trim(preg_replace('/function /i', '', $FunctieNaam));
                                    $FunctieNaam = trim(preg_replace('/\&/i', '', $FunctieNaam));

                                    if (empty($JSFuncArray[$FunctieNaam]) && !(in_array($FunctieNaam, $StdExcJSFuncArray))) {

                                        $JSFuncArray[$FunctieNaam] = 'F' . substr(md5($FunctieNaam),0, 8);

                                    }

                                }
                                    
                                if ($ReplaceJS) { 
                                    SearchVars($LineArray[$rgl]); // Search JavaScript Variables
                                }
                                
                            }

                        } else {

                            // it should be PHP file
                            echo '<b>+ Scanning File: ' . substr($fileName, 1) . '</b><br>' . "\n";
                            array_push($FileArray, substr($fileName, 1));
                            $LineArray = file($SourceDir . $fileName);
                            ob_flush();
                
                            for ($rgl = 0; $rgl<sizeof($LineArray); $rgl++) {

                                $Line = trim(strtolower($LineArray[$rgl]));
                    
                                if (($ReplaceFunctions || $ReplaceJS) && substr($Line, 0, 9) == "function " ) { // Search for Function declaration 

                                    $posEinde = strpos($Line, '(');
                                    $FunctieNaam = substr(trim($LineArray[$rgl]), 0, $posEinde);
                                    $FunctieNaam = trim(preg_replace("/function /i", "", $FunctieNaam));
                                    $FunctieNaam = trim(preg_replace("/\&/i", "", $FunctieNaam));                          
                            
                                    if($FunctieNaam == 'doLoad') {
                                        $FunctieNaam = 'doLoad';
                                    }

                                    // we have to find out if the function is JavaScript Function or PHP function
                                    // we do it by checking if function is between '<script' and '</script' tags
                                    
                                    if(findScriptTagInFile($rgl, $LineArray)) {

                                        // a JS function
                                        if (empty($JSFuncArray[$FunctieNaam]) && !(in_array($FunctieNaam,$StdExcJSFuncArray))) {
                                            $JSFuncArray[$FunctieNaam] = 'F' . substr(md5($FunctieNaam), 0, 8);
                                        }
                                        
                                    } else {

                                        // is a PHP function
                                        if (empty($FuncArray[$FunctieNaam]) && !in_array($FunctieNaam, $UdExcFuncArray)) {
                                            $FuncArray[$FunctieNaam] = 'F' . substr(md5($FunctieNaam), 0, 8);
                                        }

                                    }

                                } elseif ($ReplaceFunctions && preg_match("/^[ \t]*class[ \t]+([0-9a-zA-Z_]+)[ \t\n\r\{]/U", 
                                                                          $LineArray[$rgl], $matches)) { // Search for Class declaration

                                    // store class name to the functions array - class name has to be same as constructor name
                                    $FunctieNaam = $matches[1];

                                    if (empty($FuncArray[$FunctieNaam]) && !(in_array($FunctieNaam, $UdExcFuncArray))) {
                                        $FuncArray[$FunctieNaam] = 'F' . substr(md5($FunctieNaam), 0, 8);
                                    }

                                    if (!in_array($FunctieNaam, $ClassArray) && !in_array($FunctieNaam,$UdExcFuncArray)) {
                                        $ClassArray[] = $FunctieNaam;
                                    }

                                } elseif ($ReplaceConstants && preg_match("/define[ \t(]/i", substr($Line, 0, 7))) { // Search for Constant declaration 

                                    $posStart = strpos($Line, '(');
                                    $posEnd = strpos($Line, ",");
                                    $ConstantName = substr(trim($LineArray[$rgl]), ($posStart+1), ($posEnd - $posStart - 1));
                                    $ConstantName = preg_replace('/[\"\']/',"", $ConstantName);
                                    $posDollar = strpos($ConstantName, "$"); // name of constant may not be a variable

                                    if ($posDollar === FALSE && $ConstantName != 'SID') {

                                        // doesn't convert SID constant (PHP4)
                                        if (!($ConstArray[$ConstantName]) && !in_array($ConstantName,$UdExcConstArray)) {

                                            $ConstArray[$ConstantName] = 'C' . substr(md5($ConstantName), 0, 8);
                                        }
                                    }
                                }

                                if ($ReplaceVariables || $ReplaceJS) { SearchVars( $LineArray[$rgl]); } // *** Search Variables

                            }

                        }

                    } else {

                        // file was excluded, just copy it
                        echo '- <font color=blue>Excluded</font>, just copy Filename: ' . substr($fileName, 1) . '<br>' . "\n";
                        copy($SourceDir . $fileName, $TargetDir . $fileName);

                    }

                } elseif ($_POST['CopyAllFiles']) {

                    echo '- Copy Filename: ' . substr($fileName, 1) . '<br>' . "\n";
                    copy($SourceDir . $fileName, $TargetDir . $fileName);

                }

            } elseif ($_POST['RecursiveScan'] && is_dir($SourceDir . $fileName)) { // while looping, this file is a dir

                // check if the directory is in UdExcDirArray
                foreach($this->UdExcDirArrayRegEx as $val) {

                    // compare directory name with regular expression                    
                    if(preg_match($val, $SourceDir . $fileName)) {
                        $excludeDirectory = true;
                        break;
                    }

                }

                if (TRUE === $excludeDirectory) {

                    echo '<font color="blue">Directory ' . $SourceDir . $fileName  . ' excluded, not copied!</font><br />';

                } else {

                    die('fppp');

                    if (!is_dir($TargetDir . $fileName)) {

                        if (@mkdir($TargetDir . $fileName, 0707)) { 

                            echo 'Creating Directory : '.$TargetDir.$fileName.'.<br />';

                        } else { 

                            echo '- Creating Directory : ' . $TargetDir . $fileName . ' <font color=orange>Warning: Creation failed.</b></font><br />'; 

                        }

                    }
                    
                    $this->scan_source_files($fileName);
                }
            }
        }

        $dir->close();
    }

}

?>