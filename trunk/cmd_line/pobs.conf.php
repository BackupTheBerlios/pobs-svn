<?php

    /*

    POBS - PHP Obfuscator

    August 28th 2004

    Version: 1.00

    - AUTHOR
            - Rajesh Kumar <rks@meetrajesh.com>

    - CONFIG FILE DESIGN
            - Frank Karsten and Team (http://www.walhalla.nl)

    For the most up-to-date documentation visit:
    http://www.pobs.net/

    This program is free software; you can redistribute it and/or 
    modify it under the terms of the GNU General Public License as
    published by the Free Software Foundation; either version 2 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but
    WITHOUT ANY WARRANTY; without even the implied warranty of 
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
    General Public License for more details. 

    */



   /**
    * POBS - CONFIGURATION FILE
    *
    * README FIRST!
    * 
    * This file controls many aspects of POBS' behavior. Options passed to
    * pobs.php on the command will take precedence. POBS will try to look for
    * this configuration file in the same directory as pobs.php. Instead of a
    * real INI file, this config file is just a plain PHP script file, so
    * that pattern arrays can be specified directly.  Famiiarity with PHP's
    * syntax is assumed (Of course, this script runs only on PHP 5.0.1 or
    * greater, so you're obviously a PHP scripter).
    *  
    * Please leave the data types intact. Specify integers as integers and
    * booleans as booleans.
    * 
    * Please edit this file carefully, as any errors in parsing this file
    * will halt execution of pobs.php, and the appropriate errors sent to
    * STDERR.
    */



   /**
    * A NOTE ABOUT WILDCARDS
    *
    * Some arrays accept wildcards as parameters. These arrays look for
    * shell-style wildcards and NOT regular expressions.  Please exercise
    * care if you're a regular expression veteran. For instance, '?' refers
    * to 0 or 1 characters, '*' refers to 0 or more characters, and so
    * on. '?' and '*' have no relationships with the previous character, and
    * so '*' (you may begin an expression with '*') would match all files.
    */

//deprecated       
#    $SourceDir = '/home/user/work/pobs/test/old';
#    $TargetDir = '/home/user/work/pobs/test/new';
    
#   $TableColumns     = 5; // No longer an option
#   $TimeOut          = 5000; // Deprecated, default=20s

    $MaxFiles         = 2000;  // Maximum number of files to be processed
    $_POBSMaxRepeats  = 100;   // Maximum cycle repeats - protects against
                              // unlimited cycles, in case of condition errors

    // How long do you want function names to be?
    // If the number is too big, this will increase your file-sizes dramatically
    $MD5KeyLen      = 8;

    // Strip whitespace and comments from PHP code?
    // Note: this won't strip whitespace between heredocs and will not touch your HTML
    // Leave as boolean!

    $StripWhiteSpace = true;

    // Only files with defined extensions will be processed if you want to
    // process files without any suffix, add "." to the array
    // Example: $FileExtArray = array("*.php","*.php3","*.php4", "*.php?", "*.php[345]", "*.inc");
    // '*' will match each and every file and will slow down obfuscation considerably
    # $FileExtArray     = array('*.php', "*.inc");
    $FileExtArray     = array('*.php');

    // Sometimes, you may need certain HTML files to be in your target folder (You may be passing them to readfile())
    // But you do may not want to process an entire directory
    // You may just want to copy as-is certain inc or html files
    // Example array("docs/foobar.html", "inc/tada.php", ".js");
    $FileExtCopyArray = array('inc/*.html');
        
    // If JavaScript replacement is checked, then files with extensions 
    // specified below will be processed as well, and will be considered 
    // to contain pure JavaScript code (no PHP tags)
    // Useful if you have JS functions stored in external files
    $JSFileExtArray   = array('js');

    // Standard filename to be excluded
    // Please make sure that the extension of the file name given here 
    // exists in $FileExtArray or in $FileExtCopyArray
    $StdExcFileArray  = array('Dummy Entry');

    // JS functions that should not be replaced
    $StdExcJSFuncArray = array('Dummy Entry');

    // Do not obfuscate lines that contain specified patterns.
    // Exercise care while using this pattern. Don't specify a string that
    // can, by sheer coincidence, be part of some of your code. 
    // Matched as a string, not as a regular expression.
    // Also consider all the dependencies of non-obfuscated lines.
    // Example:
    // $LineExclude = '__POBS_EXCLUDE__';
    // then put comment containing __POBS_EXCLUDE__ to every line you dont want to obfuscate 
    // like: $val = myfunction($a, $b); // __POBS_EXCLUDE__ (this line wil be not obfuscated) 
    $LineExclude = '';  
    
    // JS variables that should not be replaced

    $StdExcJSVarArray = array(

    'Dummy Entry',
    'value',
    'selectedIndex',
    'text',
    'name',
    'color',
    'style',
    'length',
    'selection',
    'new',
    'var',
    'editObject',
    'head',
    'base',
    'keywords',
    'description',
    'src',
    'cont',
    'html',
    'forms',
    'head',
    'row',
    'i',
    'j',
    'k',
    'title',
    'content',
    'type'

    );
    
    // Standard variables that should not be replaced

    $StdExcVarArray = array(

    'GLOBALS',
    'GATEWAY_INTERFACE',
    'SERVER_NAME',
    'SERVER_SOFTWARE',
    'SERVER_PROTOCOL',
    'REQUEST_METHOD',
    'QUERY_STRING',
    'DOCUMENT_ROOT',
    'HTTP_ACCEPT',
    'HTTP_ACCEPT_CHARSET',
    'HTTP_ACCEPT_ENCODING',
    'HTTP_ENCODING',
    'HTTP_ENV_VARS',
    '_ENV',
    'HTTP_ACCEPT_LANGUAGE',
    'HTTP_CONNECTION',
    'HTTP_HOST',
    'HOST',
    'HTTP_REFERER',
    'HTTP_SERVER_VARS',
    '_SERVER',
    'HTTP_USER_AGENT',
    'REMOTE_ADDR',
    'REMOTE_PORT',
    'SCRIPT_FILENAME',
    'SERVER_ADMIN',
    'SERVER_PORT',
    'SERVER_SIGNATURE',
    'PATH_TRANSLATED',
    'SCRIPT_NAME',
    'REQUEST_URI',
    'argv',
    'argc',
    'PHPSESSID',
    'SID',
    'PHP_SELF',
    'HTTP_COOKIE_VARS',
    '_COOKIE',
    'HTTP_GET_VARS',
    '_GET',
    'HTTP_POST_VARS',
    '_POST',
    'HTTP_SESSION_VARS',
    '_SESSION',
    'HTTP_POST_FILES',
    '_FILES',
    'userfile',
    'userfile_name',
    'userfile_size',
    'userfile_type',
    'this',
    '_REQUEST',
    '__FILE__',
    '__LINE__'

    );

    // Variables whose keys will be not replaced
    // Example: For $_SERVER['REMOTE_ADDR'], the REMOTE_ADDR string will be not replaced
    $StdExcKeyArray = array(

    'Dummy Entry',
    '_SERVER',
    '_ENV',
    'HTTP_SERVER_VARS',
    'HTTP_ENV_VARS'

    );
    
    // All functions that return objects (require special handling)
    $StdObjRetFunctionsArray = array(

    'Dummy Entry',
    'mysql_fetch_object',
    'pg_fetch_object'

    );

    // Types of comments that will be replaced
    // Available types are: '/**/', '//' and '#'

    // No longer an option

    //     $StdReplaceComments = array(
    
    //     'Dummy Entry',
    //     '/**/',
    //     '//'
    
    //     );
    
    // Variables in this array will be not replaced
    // All variables less than 4 characters long are automatically excluded
    $UdExcVarArray    = array();

/*

    'Dummy Entry',
    'foo',
    'tmp',
    'x', # for($x;$x<10;++$x...
    'fp',
    'sql',
     'i',

    );

*/

    // Constants in this array will be not replaced
    $UdExcConstArray  = array('Dummy Entry');

    // Classes in this array will be not replaced
    // Note that if classes are ignored, corresponding constructor functions are NOT ignored
    // (for PHP5 compatibility where methods can have the same name as the class, provided a __construct() method
    // is found). You will need to manually add constructors of classes that are being ignored to $UdExcFuncArray
    $UdExcClassArray   = array();

    // Functions in this array will be not replaced
    $UdExcFuncArray   = array('start', 'open', 'close', 'formatTextSmileCallback');

    // - Files that will be excluded from obfuscation
    // - Can use shell regexps such as '*cat_*.php'
    // - The files will be copied to the target directory
    $UdExcFileArray   = array('Dummy Entry');

    // - Directories that will be excluded from obfuscation
    // - You can use star convention, like '/*mydirname*'
    // - It is recommended you use '/' at the start of directory names 
    //   if you want to filter directories beginning with specified strings
    // WARNING: Specified directories with all its content will be NOT processed and NOT copied to the target directory
    //          You'll need to copy them by hand if you need them in your application
    $UdExcDirArray    = array('avatars', 'board_pics', 'cache', 'css', 'images', 'utils', 'uploads');


    // Will not traverse this directory, but will copy it to target as is
    // The directories here may or may not exist in $UdExcDirArray
    // Directories found here are automatically excluded
    $UdExcDirCopyArray = array('css', 'images', 'cache', 'js', 'inc/google', 'docs');

?>