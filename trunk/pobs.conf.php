<?php

   /**
    * POBS - CONFIGURATION FILE
    * 
    * This file controls many aspects of POBS' behavior. POBS will try to
    * 'require' it and looks for it in the parent directory as pobs.php is
    * located. Instead of a real INI file, this is just a plain PHP script
    * file. It is assumed you are familiar with PHP's syntax, so editing this
    * file shouldn't pose too much trouble.
    * 
   */
       
#    $SourceDir = '/home/user/work/pobs/test/old';
#    $TargetDir = '/home/user/work/pobs/test/new';

    $SourceDir = '/home/rajesh/pobs/www/rforum';
    $TargetDir = '/home/rajesh/pobs/www/rforum_pobsed';

    $TableColumns     = 5;
    $TimeOut          = 5000;
    $MaxFiles         = 2000;  // Maximum number of files to be processed
    $_POBSMaxRepeats  = 100;   // Maximum cycle repeats - protects against
                              // unlimited cycles, in case of condition errors


    // Only files with defined extensions will be processed if you want to
    // process files without any suffix, add "." to the array
    // Example: $FileExtArray = array("php","php3","php4","php5","inc",".");
    $FileExtArray     = array('php', 'inc');
    
    // If JavaScript replacement is checked, then files with extensions 
    // specified below will be processed as well, and will be considered 
    // to contain pure JavaScript code (no PHP tags)
    // Useful if you have JS functions stored in external files
    $JSFileExtArray   = array('js');

    // Standard filename to be excluded
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

    'Dummy Entry',
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

    $StdReplaceComments = array(

    'Dummy Entry',
    '/**/',
    '//'

    );

    // Variables in this array will be not replaced
    $UdExcVarArray    = array('Dummy Entry');

    // Constants in this array will be not replaced
    $UdExcConstArray  = array('Dummy Entry');

    // Functions in this array will be not replaced
    $UdExcFuncArray   = array('Dummy Entry');
    
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
    $UdExcDirArray    = array('js');

?>