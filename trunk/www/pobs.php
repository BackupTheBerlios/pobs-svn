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

set_include_path(str_replace('.:', '../:', get_include_path()));
error_reporting(E_ALL ^ E_NOTICE);

require_once 'pobs.conf.php';
require_once 'libs/pobs_core.lib.php';
require_once 'libs/pobs_www.lib.php';

$POBS = &new PobsWWW;
$API  = &new PobsCore($POBS);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Language" content="english" />
  
  <title>POBS - A PHP Obfuscator</title>
  <link rel="stylesheet" type="text/css" media="all" href="pobs.css" />

</head>

<body>

<?php

$API->check_safe_mode();

if (isset($_POST['OK'])) {

    if (!is_readable($_POST['SourceDir'])) {

        trigger_error('Source Directory ' . $_POST['SourceDir'] . ' does not exist or is not readable', E_USER_ERROR);

    }

    if (!is_writeable($_POST['TargetDir'])) {

        trigger_error('Target Directory ' . $_POST['TargetDir'] . ' does not exist or is not writeable', E_USER_ERROR);

    }

    echo '<h3>Execute POBS: ' . $_POST['SourceDir'] . ' =&gt; ' . $_POST['TargetDir'] . '</h3>';
     
    $API->get_wild_cards();
    $API->scan_source_files();

    if (!$_POST['ReplaceClasses']) {
            
        $tempFuncArray = array();

        foreach ($API->FuncArray as $key => $value) {

            if (!in_array($key, $API->ClassArray)) {

                $tempFuncArray[$key] = $value;

            }

        }

        $API->FuncArray = $tempFuncArray;

    }

    ShowArrays();
    WriteTargetFiles();

} else {

    $POBS->show_initial_screen();

}



function findScriptTagInFile($index, $LineArray)
{
  $WholeFile = strtolower(implode("", $LineArray));
  $Line = strtolower($LineArray[$index]);
    
  $LinePos = strpos($WholeFile, $Line);
  if($LinePos === false)
    return false;
    
  $offset = 0;
  $MaxPos = false;
    
  // find closest $what string
  while(true)
  {
    $pos = strpos($WholeFile, '<script', $offset);
    if($pos === false)
      break;
        
    if($pos>$LinePos)
      break;
        
    $offset = $pos+1;
    $MaxPos = $pos;
  }
    
  if($MaxPos === false)
    return false;
    
  // found one, now check if there is not and ending tag before our line
  $pos = strpos($WholeFile, '</script', $MaxPos);
  
  if($pos === false || $pos > $LinePos )
    return true;
    
  return false;
}


function ShowArrays() {
    global $FuncArray, $VarArray, $JSVarArray, $JSFuncArray, $ConstArray, $FileArray, $UdExcVarArray, $UdExcVarArrayWild;

    echo '<hr color="#000000" height="1" noshade /><h3>Replaced elements :</h3>';

    DisplayArray( $FuncArray, "Found functions or classes that will be replaced", $BgColor="FFF0D0");
    DisplayArray( $ConstArray, "Found constants that will be replaced", $BgColor="8DCFF4");
    $VarsArr = $VarArray;
    ksort( $VarsArr );
    $JSVarsArr = $JSVarArray;
    ksort( $JSVarsArr );
    
    DisplayArray( $VarsArr, "Found variables that will be replaced", $BgColor="89CA9D");
    DisplayArray( $JSFuncArray, "Found JavaScript functions that will be replaced", $BgColor="89CA00");
    DisplayArray( $JSVarsArr, "Found JavaScript variables that will be replaced", $BgColor="89CA00");
    DisplayArray( $UdExcVarArray, "User Defined Exclude Variables", $BgColor="BFBFBF");
    DisplayArray( $FileArray, "Scanned Files", $BgColor="FA8B68");

    echo    '<br>&nbsp;<br><hr color="#000000" height=1 noshade><h3>Number of userdefined elements to be replaced :</h3>'.
                'Functions: '.sizeof( $FuncArray ).'<br>'.
                'Variables: '.sizeof( $VarArray ).'<br>'.
                'JavaScript Variables: '.sizeof( $JSVarArray ).'<br>'.
                'Constants: '.sizeof( $ConstArray ).'<br>'.
                '<br>Scanned Files: '.sizeof( $FileArray ).'<br>'.
                '&nbsp;<br>';
}


function WriteTargetFiles() {
    global $FilesToReplaceArray, $FileArray, $StartTime, $TotalFileSizeRead, $TotalFileSizeWrite;
    global $ReplaceNewer, $SourceDir, $TargetDir;
    
    echo    '<h3>Check and Replacing file :</h3>'.
                '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=3><TR>';

    $count = 0;

    foreach( $FileArray as $key => $FileName)   
    {
        $count++;
        $ReplaceFile = TRUE;

        if ( $ReplaceNewer ) {
            $FileRead = $SourceDir."/".$FileName;
            $FileWrite = $TargetDir."/".$FileName;
            if (file_exists($FileWrite)) { // *** CHECK IF SOURCEFILE IS NEWER THAN TARGETFILE 
                $FileStats = stat($FileWrite);
                $FileWriteDate = $FileStats[9];
                $FileStats = stat($FileRead);
                $FileReadDate = $FileStats[9];
                if ( $FileReadDate <= $FileWriteDate ) $ReplaceFile = FALSE;
            }
        }
        
        echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=3><TR>';
        echo '<TR><TD>'.$count.' - '.$FileName.'</TD><TD>';
        if ( $ReplaceFile ) {
            $FileStartTime = time();
            echo ': <FONT COLOR=red>Replaced</FONT>';
            ReplaceThem($FileName);
            echo ' - Elapsed Time: '.(time()-$FileStartTime).' sec.';
        }   else echo ': <FONT COLOR=green>Not replaced</FONT> (sourcefile older than targetfile).';
        echo '</TD></TR></TABLE>';
        flush();
    }

    echo '&nbsp;<br>'.
        '&nbsp;<br><hr color="#000000" height=1 noshade><h3>Stats :</h3>'.
        'Start Time: '.$StartTime.'<br>'.
        'Finish Time: '.time().'<br>'.
        '<b>Elapsed Time: '.(time()-$StartTime).' sec</b><br>'.
        '&nbsp;<br>'.
        '<b>Total FileSize of parsed Files: '.$TotalFileSizeRead.' Bytes<br>'.
        'Total FileSize of written Files: '.$TotalFileSizeWrite.' Bytes</b><br>';
}

// ** FUNCTIONS ** 

function SearchVars($Line) 
{
    global $VarArray, $StdExcVarArray, $StdExcKeyArray, $UdExcVarArray, $UdExcVarArrayWild, $UdExcVarArrayDliw, $ObjectVarArray, $JSVarArray, $StdObjRetFunctionsArray;

    // special handling for functions returning objects
    foreach($StdObjRetFunctionsArray as $key => $Value )
    {
        if ( preg_match('/\$([0-9a-zA-Z_]+)[ \t]*\=[ \t]*'.$Value.'/', $Line, $matches )) // Search for variables, that are objects
        {                                         
            // store class name to the functions array - class name has to be the same as constructor name
            $ObjectVariable = $matches[1];
            if ( !in_array($ObjectVariable, $ObjectVarArray) ) $ObjectVarArray[] = $ObjectVariable;
            $ObjectVariableEncoded ='V'.substr(md5($VarName), 0,8);
            if ( !in_array($ObjectVariableEncoded, $ObjectVarArray) ) $ObjectVarArray[] = $ObjectVariableEncoded;
        }
    }


    // search in javascript code         

           
    preg_match_all('/var[ \t]+([0-9a-zA-Z_]+)[ \t]*[\=;]+/', $Line, $matches);
//    preg_match_all('/var(?:[ \t]+|[ \t\,\=a-zA-Z0-9_]+[ \t,])([a-zA-Z0-9_]+)[ \t]*[\=\;\,]/', $Line, $matches);

    foreach($matches[1] as $mkey)
    {
        $orig = $mkey;
        $VarName = $orig;

        if (!$JSVarArray[$VarName] && !(in_array($VarName,$StdExcVarArray)) && !(in_array($VarName,$UdExcVarArray)))
        { 
            // check in Wildcards Array
            foreach( $UdExcVarArrayWild as $key => $Value )
           {
               if (substr($VarName, 0, strlen($Value)) == $Value )
              {
                  echo 'Variable with name '.$VarName.' added to $UdExcVarArray.<br>';
                  array_push( $UdExcVarArray, $VarName ); // add to excluded Variables array
              }
           }

          // SB check in Dliwcards Array (the wild part's on the front)
          foreach( $UdExcVarArrayDliw as $key => $Value )
         {
             if (substr($VarName, 0 - strlen( $Value ) ) == $Value )
             {
                 echo 'Variable with name '.$VarName.' added to $UdExcVarArray.<br>';
                 array_push( $UdExcVarArray, $VarName ); // add to excluded Variables array
             }
         }

         if (!(in_array($VarName,$UdExcVarArray)))   // check again in Excluded Variables Array
             $JSVarArray[$VarName]= 'V'.substr(md5($VarName), 0,8);
        }
    }


  while (preg_match('/\$([0-9a-zA-Z_]+)/', $Line, $regs))
    {
        
        $VarName = $regs[1];
        if (!$VarArray[$VarName] && !(in_array($VarName,$StdExcVarArray)) && !(in_array($VarName,$UdExcVarArray)))
        { 
            // check in Wildcards Array
            foreach( $UdExcVarArrayWild as $key => $Value )
            {
                if (substr($VarName, 0, strlen($Value)) == $Value )
                {
                    echo 'Variable with name '.$VarName.' added to $UdExcVarArray.<br>';
                                        array_push( $UdExcVarArray, $VarName ); // add to excluded Variables array
                }
            }

            // SB check in Dliwcards Array (the wild part's on the front)
            foreach( $UdExcVarArrayDliw as $key => $Value )
            {
                if (substr($VarName, 0 - strlen( $Value ) ) == $Value )
                {
                    echo 'Variable with name '.$VarName.' added to $UdExcVarArray.<br>';
                    array_push( $UdExcVarArray, $VarName ); // add to excluded Variables array
                                }
                        }

                        if (!(in_array($VarName,$UdExcVarArray))) // check again in Excluded Variables Array
                $VarArray[$VarName]= 'V'.substr(md5($VarName), 0,8);
                }

                $Line = substr($Line, ( strpos($Line,'$') + 1 ) );
    }
}

class CommentHandler
{
	var $comments=array();
	var $keep_first=0;
	var $found=0;
	var $replaced=0;
	
	//-------------------------------------------
	// public
	//-------------------------------------------
	
	//initialise class, tell it how many comments
	//you wish to preserve
	function CommentHandler($keep_first)
	{
		$this->keep_first=$keep_first;
	}

	//tell it how many comments
	//you wish to preserve
	function SetKeepFirst($keep_first)
	{
		$this->keep_first=$keep_first;
	}
    
	//remove comments from string, replacing the first
	//n comments with placeholders
	function RemoveComments(&$contents)
	{
        global $StdReplaceComments;

		$this->comments=array();
		$this->found=0;
		$this->replaced=0;
		
		//because we use multiple regexps to spot the comments
		//we can't be sure which ones come first, so we replace
		//each comment with a placeholder. During the 
		//RestoreComments phase, we *can* know which comments are
		//first, and can decide whether or not to restore the original
		
                if(in_array('//', $StdReplaceComments))
                {
		    // REMOVE COMMENTS //, EXCEPT '//-->'
		    $contents = preg_replace( "/[ \t\n]+(\/\/)(?![ \t]*-->)[^\n]*/me", 
//		    $contents = preg_replace( "/(\/\/)(?![ \t]*-->)[^\n]*/me", 
			                      "\$this->StoreComment('\\0')", $contents); 
                }


                if(in_array('#', $StdReplaceComments))
                {
		    // REMOVE COMMENTS #
		    $contents = preg_replace( "/[ \t\n]+(\#)[^\n]*/sme", 
			                      "\$this->StoreComment('\\0')", $contents); 
                }

                // REMOVE COMMENTS /* ... */
                if(in_array('/**/', $StdReplaceComments))
                {
                    $contents = preg_replace( '/\/\*.*?\*\/[ \n]*/sme', 
	    	                              "\$this->StoreComment('\\0')", $contents); 
                }
	}
	
	//restore the first n comments
	function RestoreComments(&$contents)
	{
		$contents = preg_replace( '/___POBS_COMMENT_(\d+)/e', 
	    	"\$this->FetchComment('\\1')", $contents); 
	
	}
	
	
	
	//-------------------------------------------
	// private
	//-------------------------------------------
	
	function StoreComment($comment)
	{
		//store the comment and return a placeholder
		//this allows us to preserve the format of 
		//comments when POBS removes white space
    $this->comments[$this->found]=$comment;

    $replacement = '';
    
    if( ($pos = strpos($comment,'?>')) !== false && strpos($comment,'<?') === false)
    {
      $comment = substr($comment, 0, $pos);
      $replacement = '?>';
    }
    // it it is // type of comment, change it to /* */ type
    if($comment[0]=='/' && $comment[1]=='/')
    {
      $comment[1] = '*';
      $comment .= '*/ ';
    }
    
    $this->comments[$this->found]=$comment;
    $replacement="___POBS_COMMENT_".$this->found." ".$replacement;
    
    $this->found++;
				
  	return $replacement;
	}

	function FetchComment($idx)
	{
		if ($this->replaced<$this->keep_first)
		{
			$this->replaced++;
			return $this->comments[$idx]; 
		}
		return "";
	}
}

/* 
   we have to make sure that lines will be not longer than some constant,
   otherwise it can make problems with PHP
*/
function Concatenate($contents, $MaxCharsInLine = 100)
{
  $linelength = 0;
  $replaced = 0;
  
  // get rid of useless lines first
  $contents = preg_replace( "/___POBS_NEWLINE___[ \t]*___POBS_NEWLINE___/m", "___POBS_NEWLINE___", $contents);
  
  while(($pos = strpos($contents, "___POBS_NEWLINE___")) !== false)
  {
    if($pos-$linelength<$MaxCharsInLine)
    {

      // replace with space
      $head = substr($contents, 0, $pos);
      $tail = substr($contents, $pos+18);
      $contents = $head.' '.$tail;
      $replaced++;
    }
    else
    {
      // replace with newline
      $head = substr($contents, 0, $pos);
      $tail = substr($contents, $pos+18);
      $contents = $head."\n".$tail;
      $linelength = $pos;
      $replaced++;
    }
  }
  
  // get rid of multiple spaces
  $contents = preg_replace( "/[ \t]+/", ' ', $contents);
    
  return $contents;
}


function ReplaceThem($FileName)
{
    global $VarArray, $JSVarArray, $JSFuncArray, $FuncArray, $JSFileExtArray, $FileExtArray, $ConstArray, $SourceDir, $TargetDir, $ObjectVarArray, $ReplaceVariables, $ReplaceJS;
    global $ReplaceConstants, $ReplaceFunctions, $RemoveIndents, $RemoveComments, $ConcatenateLines, $StdExcKeyArray, $StdExcJSVarArray, $StdExcJSFuncArray;
    global $KeptCommentCount, $NewlinesReplaced, $_POBSMaxRepeats;
    global $LineExclude, $ExcludedLines;
    global $CopyrightText, $CopyrightPHP, $CopyrightJS;
  	
    $FileRead = $SourceDir."/".$FileName;
    $FileWrite = $TargetDir."/".$FileName;
    
    // check if file has the proper suffix
    $extpos = strrpos($FileName, ".");
    
    if($extpos>0)
      $Suffix = substr($FileName,$extpos+1);
    else
      $Suffix = md5(rand()); // generate some non existing extension

    $NewlinesReplaced = 0;

    $FdRead = fopen( $FileRead, 'rb' );

    $contents_arr = file($FileRead);
    $contents = '';
    $LinesExcluded = 0;
    $ExcludedLines = array();
    
    // take care of lines that should be excluded from obfuscation
    if($LineExclude == '')
      $contents = fread( $FdRead, filesize( $FileRead ) );
    else
    {
      for($i=0; $i<count($contents_arr); $i++)
      {
        // check if line should be excluded
        if(strpos($contents_arr[$i], $LineExclude) !== false)
        {
          $ExcludedLines[$LinesExcluded] = $contents_arr[$i];
          $contents .= '__POBS_@LINE@_EXCLUDED_'.$LinesExcluded;
          $LinesExcluded++;
        }
        else
          $contents .= $contents_arr[$i];        
      }
    }
      
//    $contents = fread( $FdRead, filesize( $FileRead ) );
    $GLOBALS['TotalFileSizeRead'] += filesize( $FileRead );
    echo ' - Size:'.filesize( $FileRead );
    fclose( $FdRead );

    $ch=new CommentHandler($KeptCommentCount);
   
    // we have to process comments in any case
    $ch->RemoveComments($contents);
    
    $contents = preg_replace( "/[\r\n]{2,}/m", "\n", $contents ); // REMOVE EMPTY LINES AND DOS "\r\n"
    $contents = preg_replace( "/[ \t]{2,}/m", ' ', $contents ); // REMOVE TOO MANY SPACE OR TABS (but also in output text...)

    if ($RemoveIndents) 
    {
        $contents = preg_replace( "/([;\}]{1})\n[ \t]*/m", "\\1\n", $contents);  // REMOVE INDENT TABS and SPACES
    }

    if ( strpos( $contents, '->') || strpos( $contents, '::') ) 
        $ReplaceObjects = TRUE;
    else 
        $ReplaceObjects = FALSE;

    if ( preg_match('/class/i', $contents) ) 
    {
        $ReplaceClasses = TRUE;
    }
    else 
        $ReplaceClasses = FALSE;

    // *** REPLACE FUNCTIONNAMES
    if ( $ReplaceFunctions)
    {
        foreach( $FuncArray as $key => $Value ) 
        {
            if ( strlen($key) && strpos(strtolower($contents), strtolower($key)) !== FALSE ) // to speed up things, check if variable name is, in any way, present in the file
            {
                $contents = preg_replace("/([^a-zA-Z0-9_]+)".$key."[ \t]*\\(/i","\\1".$Value."(", $contents); //werkt

                if ($ReplaceObjects)
                {
                    $contents = preg_replace('/([^a-zA-Z0-9_]+)('.$key.')::/','\1'.$Value.'::', $contents); // objects
                }
                if ($ReplaceClasses)
                {
                    $contents = preg_replace('/([^0-9a-zA-Z_])class[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1class '.$Value.'\3', $contents); // class declaration
                 }

                 $contents = preg_replace('/([^0-9a-zA-Z_])extends[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1extends '.$Value.'\3', $contents); // extended or derived class declaration
                 $contents = preg_replace('/([^0-9a-zA-Z_])new[ \t]+('.$key.')([^0-9a-zA-Z_(])/i','\1new '.$Value.'\3', $contents); // extended or derived class declaration
            }
        }
    }

    // *** REPLACE VARIABLENAMES
    if ( $ReplaceVariables )
    {
        if ( stristr($contents, 'name=' ) ) 
        {
            $ReplaceFieldnames = TRUE;
        }   
        else 
            $ReplaceFieldnames = FALSE;

        foreach( $VarArray as $key => $Value )
        {
            if ( strlen($key) && strpos(strtolower($contents), strtolower($key)) !== FALSE ) // to speed up things, check if variable name is, in any way, present in the file
            {
                $contents = preg_replace('/([$&?{])('.$key.')([^0-9a-zA-Z_])/m','\1'.$Value.'\3', $contents);  // normal variables and parameters
                $contents = preg_replace('/(&amp;)('.$key.')([^0-9a-zA-Z_])/m','\1'.$Value.'\3', $contents);  // variable in <A> tag with &amp;

                // process javascript code                
                preg_match_all('/\<SCRIPT.*>(.*)<\/SCRIPT>/Uis',$contents,$matches);  // in case there are more <SCRIPT> sections within one file

                foreach($matches[1] as $mkey)
                {
                  $tcount++;
                  $orig = $mkey;

                  $replaced = $orig;

                  if ( !in_array($key, $StdExcJSVarArray) )
                  {
                     $replaced = preg_replace('/(.*?[ \.])('.$key.')([ \t\.\=\!].*)/is','\1'.$Value.'\3', $orig);  // javascript variables
//                  $replaced = preg_replace('/(\=[ \t]*)('.$key.')([ \t]*[\;\.])/is','\1'.$Value.'\3', $replaced);  // javascript variables

//                  $replaced = preg_replace('/(.*var[ \t\,a-zA-Z0-9_]+)('.$key.')([ \t]*[\=\;\,])/Uis','\1'.$Value.'\3', $replaced);  // javascript var defines (var XXX;)
//                  $replaced = preg_replace('/([^0-9a-zA-Z_])('.$key.')([ \t]*[\+\-\*\/\[\;\,\.\)])/is','\1'.$Value.'\3', $replaced);  // javascript arrays (xxx[])    // \= MISSING
//                  $replaced = preg_replace('/((?:\[|\[[ \t\'\"\+\-\*\/a-zA-Z0-9_]*[^a-zA-Z0-9_]))('.$key.')((?:\]|[^a-zA-Z0-9_][ \t\'\"\+\-\*\/a-zA-Z0-9_]*\]))/is','\1'.$Value.'\3', $replaced);  // javascript arrays ([xxx])

//                  $replaced = preg_replace('/((?:\(|\([^\)]*[ \t\,\+\-\.\*\/\!\<\>\=]))('.$key.')((?:\)|[ \t\,\+\-\*\/\!\=\<\>][^\)]*\)))/Uis','\1'.$Value.'\3', $replaced);  // javascript function parameters
                  
                  }

                  if($orig!==$replaced)
                  {
                    $contents = str_replace($orig, $replaced, $contents);
                  }
                }

                // replace javascript code in onXXX event handlers
                if (!in_array($key, $StdExcJSVarArray))
                {
                  $tcount = 0;
                  while($tcount<$_POBSMaxRepeats && preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui', $contents))
                  {
                    $contents = preg_replace('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui','\1'.$Value.'\3', $contents);  // javascript event function parameters
                    $tcount++;
                  }
/*
                  if(preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui', $contents, $matches))
                  {
                    echo HTMLSpecialChars("<BR> matches=".$matches[1].",".$key.",".$matches[3].",<BR>");
                  }
*/
                }
                   
                $contents = preg_replace('/\$(GLOBALS|HTTP_COOKIE_VARS|HTTP_POST_VARS|HTTP_GET_VARS|HTTP_SESSION_VARS|_REQUEST|_FILES|_SERVER|_ENV|_POST|_COOKIE|_GET|_SESSION)([ \t]*)\[(["\' \t]*)'.$key.'(["\' \t]*)\]/m', '$\1[\3'.$Value.'\4]', $contents ); // var in Tabs
                $contents = preg_replace('/(setcookie|session_register|session_is_registered|session_unregister)(?:[ \t]*)\(([\\\"\']*)'.$key.'([\\\"\'\, \t)]*)/i', '\1(\2'.$Value.'\3', $contents ); // cookie or session variables

                if ($ReplaceObjects)
                {
                    $contents = preg_replace('/->[ \t]*('.$key.')(?:!\()/','->'.$Value, $contents); // objects
                    $contents = preg_replace('/::[ \t]*('.$key.')(?:![^0-9a-zA-Z_])/','::'.$Value, $contents); // objects

                    // special handling for object variables
                    if( preg_match('/\$([0-9a-zA-Z_]+)[ \t]*->[ \t]*('.$key.')[ \t]*([^0-9a-zA-Z_])/', $contents, $matches) ) // class variables
                    {
                        // check if variable is not returned from object returning function
                        $tempVar = $matches[1];
                        if(!in_array($tempVar, $ObjectVarArray) )  // XX->YY : replace YY only if XX is not in $ObjectVarArray
                            $contents = preg_replace('/(\$[0-9a-zA-Z_]+)[ \t]*->[ \t]*('.$key.')[ \t]*([^0-9a-zA-Z_])/','\1->'.$Value.'\3', $contents); // class variables
                    }

                }

               if ($ReplaceFieldnames) 
                  $contents = preg_replace('/([ \t\"\'](?:(?i)name)=[\\\"\' \t]*)'.$key.'([\\\"\'> \t])/','\1'.$Value.'\2', $contents); // input fields
            }
        }

    }
    
    // *** REPLACE JavaScript VARIABLENAMES
    if ( $ReplaceJS )
    {
        foreach( $JSVarArray as $key => $Value )
        {
            if ( strlen($key) && strpos(strtolower($contents), strtolower($key)) !== FALSE ) // to speed up things, check if variable name is, in any way, present in the file
            {
              if (in_array($Suffix, $JSFileExtArray))
              { 
                // for JS files dont need to search for script tags
                $orig = $contents;
 
                $replaced = $orig;
                
                if ( !in_array($key, $StdExcJSVarArray) )
                {
                  
                  $replaced = preg_replace('/(.*?[ \.])('.$key.')([ \t\.\=\!].*)/is','\1'.$Value.'\3', $orig);  // javascript variables
                  $replaced = preg_replace('/(\=[ \t]*)('.$key.')([ \t]*[\;\.])/is','\1'.$Value.'\3', $replaced);  // javascript variables
                  
                  // $replaced = preg_replace('/(.*var[ \t\,a-zA-Z0-9_]+)('.$key.')([ \t]*[\=\;\,])/Uis','\1'.$Value.'\3', $replaced);  // javascript var defines (var XXX;)
                  $replaced = preg_replace('/(.*var(?:[ \t]+|[ \t\,\=a-zA-Z0-9_]+[^a-zA-Z0-9_]))('.$key.')([ \t]*[\=\;\,])/Uis','\1'.$Value.'\3', $replaced);  // javascript var defines (var XXX;)
                  $replaced = preg_replace('/([^0-9a-zA-Z_])('.$key.')([ \t]*[\+\-\*\/\[\;\,\.\\=)])/is','\1'.$Value.'\3', $replaced);  // javascript arrays (xxx[])    // \= MISSING
                  $replaced = preg_replace('/((?:\[|\[[ \t\'\"\+\-\*\/a-zA-Z0-9_]*[^a-zA-Z0-9_]))('.$key.')((?:\]|[^a-zA-Z0-9_][ \t\'\"\+\-\*\/a-zA-Z0-9_]*\]))/is','\1'.$Value.'\3', $replaced);  // javascript arrays ([xxx])
                  
                  $replaced = preg_replace('/((?:\(|\([^\)]*[ \t\,\+\-\.\*\/\!\<\>\=]))('.$key.')((?:\)|[ \t\,\+\-\*\/\!\=\<\>][^\)]*\)))/Uis','\1'.$Value.'\3', $replaced);  // javascript function parameters
                  
                }
                
                
                if($orig!==$replaced)
                  $contents = $replaced;
                
                
                // replace javascript code in onXXX event handlers
                if ( !in_array($key, $StdExcJSVarArray) )
                {
                  $tempcount = 0;
                  while(preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui',$contents) && $tempcount<500)
                  {
                    $contents = preg_replace('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui','\1'.$Value.'\3', $contents);  // javascript event function parameters
                    $tempcount++;
                  }
                }
              }
              else
              {
                // process only code within <script></script> tags
                // process javascript code                
                preg_match_all('/\<SCRIPT.*>(.*)<\/SCRIPT>/Uis',$contents,$matches);  // in case there are more <SCRIPT> sections within one file

                foreach($matches[1] as $mkey)
                {
                  $tcount++;
                  $orig = $mkey;

                  $replaced = $orig;

                  if ( !in_array($key, $StdExcJSVarArray) )
                  {
                      $replaced = preg_replace('/(.*?[ \.])('.$key.')([ \t\.\=\!].*)/is','\1'.$Value.'\3', $orig);  // javascript variables
                      $replaced = preg_replace('/(\=[ \t]*)('.$key.')([ \t]*[\;\.])/is','\1'.$Value.'\3', $replaced);  // javascript variables

//                      $replaced = preg_replace('/(.*var[ \t\,a-zA-Z0-9_]+)('.$key.')([ \t]*[\=\;\,])/Uis','\1'.$Value.'\3', $replaced);  // javascript var defines (var XXX;)
                      $replaced = preg_replace('/(.*var(?:[ \t]+|[ \t\,\=a-zA-Z0-9_]+[^a-zA-Z0-9_]))('.$key.')([ \t]*[\=\;\,])/Uis','\1'.$Value.'\3', $replaced);  // javascript var defines (var XXX;)
                      $replaced = preg_replace('/([^0-9a-zA-Z_])('.$key.')([ \t]*[\+\-\*\/\[\;\,\.\\=)])/is','\1'.$Value.'\3', $replaced);  // javascript arrays (xxx[])    // \= MISSING
                      $replaced = preg_replace('/((?:\[|\[[ \t\'\"\+\-\*\/a-zA-Z0-9_]*[^a-zA-Z0-9_]))('.$key.')((?:\]|[^a-zA-Z0-9_][ \t\'\"\+\-\*\/a-zA-Z0-9_]*\]))/is','\1'.$Value.'\3', $replaced);  // javascript arrays ([xxx])

                      $replaced = preg_replace('/((?:\(|\([^\)]*[ \t\,\+\-\.\*\/\!\<\>\=]))('.$key.')((?:\)|[ \t\,\+\-\*\/\!\=\<\>][^\)]*\)))/Uis','\1'.$Value.'\3', $replaced);  // javascript function parameters

                  }

                    
                  if($orig!==$replaced)
                  {
                    $contents = str_replace($orig, $replaced, $contents);
                  }
                }

                // replace javascript code in onXXX event handlers
                if ( !in_array($key, $StdExcJSVarArray) )
                {
                  $tempcount = 0;
                  while(preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui',$contents) && $tempcount<500)
                  {
                    $contents = preg_replace('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui','\1'.$Value.'\3', $contents);  // javascript event function parameters
                    $tempcount++;
                  }
                }
              }
            }
        }
    }


    // *** REPLACE JavaScript FUNCTIONS
    if ( 1 )
    {
        foreach( $JSFuncArray as $key => $Value )
        {
            if ( strlen($key) && strpos(strtolower($contents), strtolower($key)) !== FALSE ) // to speed up things, check if variable name is, in any way, present in the file
            {
              if (in_array($Suffix, $JSFileExtArray))
              { 
                // for JS files dont need to search for script tags
                // process javascript code
                if ( !in_array($key, $StdExcJSFuncArray) )
                {
                  $contents = preg_replace("/([^a-zA-Z0-9_]+)".$key."[ \t]*\\(/i","\\1".$Value."(", $contents); //werkt

                  if ($ReplaceObjects)
                    $contents = preg_replace('/('.$key.')::/',$Value.'::', $contents); // objects

                  if ($ReplaceClasses)
                    $contents = preg_replace('/([^0-9a-zA-Z_])class[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1class '.$Value.'\3', $contents); // class declaration

                  $contents = preg_replace('/([^0-9a-zA-Z_])extends[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1extends '.$Value.'\3', $contents); // extended or derived class declaration
                  $contents = preg_replace('/([^0-9a-zA-Z_])new[ \t]+('.$key.')([^0-9a-zA-Z_(])/i','\1new '.$Value.'\3', $contents); // extended or derived class declaration
                }
                
                // replace javascript code in onXXX event handlers
                if ( !in_array($key, $StdExcJSFuncArray) )
                {
                  $tempcount = 0;
                  while(preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui',$contents) && $tempcount<500)
                  {
                    $contents = preg_replace('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui','\1'.$Value.'\3', $contents);  // javascript event function parameters
                    $tempcount++;
                  }
                }
                
              }
              else
              {
                // process only code within <script></script> tags
                preg_match_all('/\<SCRIPT.*>(.*)<\/SCRIPT>/Uis',$contents,$matches);  // in case there are more <SCRIPT> sections within one file

                foreach($matches[1] as $mkey)
                {
                  $tcount++;
                  $orig = $mkey;

                  $replaced = $orig;

                  if ( !in_array($key, $StdExcJSFuncArray) )
                  {
                    $contents = preg_replace("/([^a-zA-Z0-9_]+)".$key."[ \t]*\\(/i","\\1".$Value."(", $contents); //werkt
                    
                    if ($ReplaceObjects)
                      $contents = preg_replace('/('.$key.')::/',$Value.'::', $contents); // objects
                    
                    if ($ReplaceClasses)
                      $contents = preg_replace('/([^0-9a-zA-Z_])class[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1class '.$Value.'\3', $contents); // class declaration
                    
                    $contents = preg_replace('/([^0-9a-zA-Z_])extends[ \t]*('.$key.')([^0-9a-zA-Z_])/i','\1extends '.$Value.'\3', $contents); // extended or derived class declaration
                    $contents = preg_replace('/([^0-9a-zA-Z_])new[ \t]+('.$key.')([^0-9a-zA-Z_(])/i','\1new '.$Value.'\3', $contents); // extended or derived class declaration
                    
                  }
                }
                
                // replace javascript code in onXXX event handlers
                if ( !in_array($key, $StdExcJSFuncArray) )
                {
                  $tempcount = 0;
                  while(preg_match('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui',$contents) && $tempcount<500)
                  {
                    $contents = preg_replace('/(\<[^\?][^\>]*on[0-9a-zA-Z_]+[ \t]*\=[ \t]*[\"\']{0,1}[^\>]*[^a-zA-Z0-9_]+)('.$key.')([^a-zA-Z0-9_]+)/Ui','\1'.$Value.'\3', $contents);  // javascript event function parameters
                    $tempcount++;
                  }
                }
                
              }
            }
        }
    }

    
    // *** REPLACE CONSTANTNAMES
    if ( $ReplaceConstants )
    {
        foreach( $ConstArray as $key => $Value ) 
        {
            if ( strlen($key) && strpos(strtolower($contents), strtolower($key)) !== FALSE ) // to speed up things, check if variable name is, in any way, present in the file
            {
                $contents = preg_replace('/([^a-zA-Z0-9_\$])('.$key.')([^a-zA-Z0-9_])/', '\1'.$Value.'\3', $contents );
                
                // special handling for arrays like HTTP_SERVER_VARS
                foreach($StdExcKeyArray as $KeyArray)
                {
                  // check, if currently replaced variable is in this field
                  if(preg_match('/(\$'.$KeyArray.'\[[ \t]*[\\\'\"]+)'.$Value.'([\\\'\"]+[ \t]*\])/', $contents))
                  {
                    // restore previous value of the key
                    $contents = preg_replace('/(\$'.$KeyArray.'\[[ \t]*[\\\'\"]+)'.$Value.'([\\\'\"]+[ \t]*\])/', '\1'.$key.'\2', $contents );
                  }
                }
            }
        }
    }

    if(!$RemoveComments)
    {
      $ch->SetKeepFirst(99999);
    }

    //restore the first $KeptCommentCount comments
    $ch->RestoreComments($contents);
  

    if ($ConcatenateLines) 
    {
	    $contents = preg_replace( '/\n/sme', "___POBS_NEWLINE___", $contents); 
      $contents = Concatenate($contents);
    }   
    
    // replace placeholders with excluded lines
    if($LineExclude != '' && count($ExcludedLines)>0)
    {
      for($i=0; $i<count($ExcludedLines); $i++)
      {
        $contents = str_replace('__POBS_@LINE@_EXCLUDED_'.$i, $ExcludedLines[$i], $contents);
      }
    }

    // now add copyright text
    if($CopyrightJS == true && in_array($Suffix, $JSFileExtArray))
    {
      $contents = $CopyrightText."\n".$contents;
    }
    else if($CopyrightPHP == true && in_array($Suffix, $FileExtArray))
    {
      $contents = $CopyrightText."\n".$contents;
    }
    
    $FdWrite = fopen( $FileWrite, 'w' );
    $NumberOfChars = fwrite( $FdWrite, $contents );
    fclose( $FdWrite );
    clearstatcache();
    $GLOBALS['TotalFileSizeWrite'] += filesize( $FileWrite );
}

function DisplayArray($ArrayName, $HeaderText="", $BgColor="FFF0D0")
{
    global $TableColumns;
    
    $sizeOf = sizeOf( $ArrayName );
    
    echo    '<br>'."\n".
                '<TABLE WIDTH="100%" BORDER=0 CELLSPACING=1 CELLPADDING=3 BGCOLOR="#000000"><TR><TD><FONT COLOR=#FFFFFF><b>'.$HeaderText.'</b></FONT></TD></TR></TABLE>';
    if ( $sizeOf )
    {
        if ( $sizeOf > $TableColumns ) $width = $TableColumns; else $width = $sizeOf;
        $width = 100 / $width;

        echo '<TABLE WIDTH="100%" BORDER=0 CELLSPACING=1 CELLPADDING=3 BGCOLOR="#000000"><TR>';
    
        $Cnt = 0;
        $Line = 0;
        foreach( $ArrayName as $key => $Value )
        {   
            $Cnt++;
            echo '<TD WIDTH="'.$width.'%" BGCOLOR="#'.$BgColor.'"><b>'.$key.'</b><br>'.$Value.'</TD>';
            if ( ( $Cnt % $TableColumns) == 0  && ( $Cnt != $sizeOf ) )
            {
                echo '</TR>';
                echo '<TR>';
                $Line ++;
            }
        }
        $i = $Cnt % $TableColumns;
        if ( $i && $Line ) for ( ; $i < $TableColumns; $i++ ) echo '<TD BGCOLOR=#'.$BgColor.'>&nbsp;</TD>';
        
        echo '</TR></TABLE>'."\n";
        flush();
    }
        else echo '<i>No match or no replace requested</i><br>';
}


?>

</body>
</html>