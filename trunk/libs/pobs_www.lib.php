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

class PobsWWW {

    var $API;

    public function safe_mode_warning() {

        return '<p class="warning">SafeMode is on. Can not set timeout.</p>';

    }

    public function show_initial_screen() {

        global $API;
        $this->API = $API;

        global $TimeOut, $FileExtArray, $JSFileExtArray, $TargetDir, $SourceDir, $UdExcFuncArray, $UdExcVarArray, $UdExcConstArray, $StdObjRetFunctionsArray;
        global $ReplaceFunctions, $ReplaceConstants, $ReplaceVariables, $RemoveComments, $RemoveIndents, $ConcatenateLines;
        global $FilesToReplaceArray, $UdExcFileArray, $UdExcDirArray;

    ?>
        
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td bgcolor="#6699CC" valign="top">
              <a href="http://pobs.mywalhalla.net" target="_new"><img src="pobs_logo.gif" hspace="20" width="150" height="61" border="0"></a>
            <td>
            <td bgcolor="#6699CC" valign="top"><br /><strong>A PHP Obfuscator<br />Version 1.00</td>
          </tr>
        </table>

        <table cellpadding="3" width="100%" cellspacing="0" border="1" bordercolor="#000000">
          <tr>
            <td bgcolor="#6699CC" valign="top">
              <center><div style="font-size: 13pt;"><strong>Settings</div></center>
            </td>
          </tr>
          <tr>
            <td>
              <center>For the most up-to-date documentation, visit <a href="http://pobs.mywalhalla.net" target="STD">http://pobs.mywalhalla.net</a></center>
            </td>
          </tr>
        </table><br />

        <table cellpadding="3" width="100%" cellspacing="0" border="0">
          <tr>
            <td width="60%" valign="top">
              <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000">
                <form method="post" action="<?=$_SERVER['PHP_SELF']?>">
                  <tr>
                    <td bgcolor="#e6e6e6" valign="top"><strong>TimeOut (sec)</strong></td>
                  </tr>
                  <tr>
                    <td><?=$TimeOut?></td>
                  </tr>
                  <tr>
                    <td bgcolor="#e6e6e6" valign="top"><strong>Source Directory</strong></td>
                  </tr>
                  <tr>
                    <td><input type="text" name="SourceDir" value="<?=htmlspecialchars($SourceDir)?>" size="70"></td>
                  </tr>
                  <tr>
                    <td bgcolor="#e6e6e6" valign="top"><strong>Target Directory</strong></td>
                  </tr>
                  <tr>
                    <td><input type="text" name="TargetDir" value="<?=htmlspecialchars($TargetDir)?>" size="70"></td>
                  </tr>
                  <tr>
                    <td bgcolor="#E6E6E6" valign="top">
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td width="50%"><strong>Allowed File Extensions</strong></td>
                          <td width="50%"><strong>Allowed JavaScriptFile Extensions</strong></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">

                        <?php
      
                        $maxcount = count($FileExtArray) > count($JSFileExtArray) ? count($FileExtArray) : count($JSFileExtArray);
                   
                        for ($i=0; $i < $maxcount; $i++) {
           
                            echo '<tr>';
                            echo '<td width="50%">' . ($FileExtArray[$i] != '' ? $i . ': ' . $FileExtArray[$i] : '&nbsp;') . '</td>';
                            echo '<td width="50%">' . ($JSFileExtArray[$i] != '' ? $i . ': ' . $JSFileExtArray[$i] : '&nbsp;') . '</td>';
                            echo '</tr>' . "\n";
           
                        } ?>
      
                      </table>
                    </td>
                  </tr>

                  <tr><td bgcolor="#E6E6E6" valign="top"><strong>Replacements</strong></td></tr>
                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="2">
                        <tr>
                          <td width="130" valign="bottom">Classes</td>
                          <td width="10">&nbsp;</td>
                          <td><input type="checkbox" name="ReplaceClasses" checked="checked"></td>
                        </tr>
                        <tr>
                          <td valign="bottom">Functions</td>
                          <td width="10">&nbsp;</td>
                          <td><input type="checkbox" name="ReplaceFunctions" checked="checked"></td></tr>
                        <tr>
                          <td valign="bottom">Constants</td>
                          <td width="10">&nbsp;</td>
                          <td><input type="checkbox" name="ReplaceConstants" checked="checked"></td></tr>
                        <tr>
                          <td valign="bottom">Variables</td>
                          <td width="10">&nbsp;</td>
                          <td><input type="checkbox" name="ReplaceVariables" checked="checked"></td>
                        </tr>
                        <tr>
                          <td valign="bottom">JavaScript (Functions & Variables)</td>
                          <td width="10" valign="top">&nbsp;</td>
                          <td>
                            <input type="checkbox" name="ReplaceJS" checked="checked">&nbsp;&nbsp;
                            <?php echo '+ files with extensions: '; 
                              foreach ($JSFileExtArray as $key => $val) { echo '<strong>' . $val . '</strong>,'; } ?>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>

                  <tr><td bgcolor="#E6E6E6" valign="top"><strong>Removals</strong></td></tr>

                  <tr>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="2">
                        <tr>
                          <td width="130" valign="bottom">Comments</td>
                          <td width="10">&nbsp;</td>
                          <td>
                            <input type="checkbox" name="RemoveComments" checked="checked">
                            (Always preserve first <input type="text" size="3" name="KeptCommentCount" value="0"> comments)
                          </td>
                        </tr>
                        <tr><td valign="bottom">Indents</td><td width="10">&nbsp;</td><td><input type="checkbox" name="RemoveIndents" checked="checked"></td></tr>
                        <tr><td valign="bottom">Returns</td><td width="10">&nbsp;</td><td><input type="checkbox" name="ConcatenateLines"></td></tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td bgcolor="#E6E6E6" valign="top"><strong>File system</strong></td>
                  </tr>
                  <tr>
                    <td>
                      <input type="checkbox" name="ReplaceNewer" checked="checked">Replace edited files only<br />
                      <input type="checkbox" name="RecursiveScan" checked="checked">Recursive scan (into sub-directory)<br />
                      <input type="checkbox" name="CopyAllFiles" checked="checked">Copy all files (not in allowed file extensions) from source to dest <br />
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <strong>Copyright Text</strong> (to put on top of every processed file)<br />
                      <input type="checkbox" name="CopyrightPHP">on top of PHP files<br />
                      <input type="checkbox" name="CopyrightJS">on top of JavaScript files<br />
                      <textarea name="CopyrightText" rows="3" cols="60"></textarea>
                    </td>
                  </tr>                                
                  <tr>
                    <td bgcolor="#E6E6E6" align="center" valign="top">
                      <input type="submit" name="OK" value="Start processing">
                    </td>
                  </tr>
                </form>
              </table>
            </td>

            <td width="20%" valign="top">
              <table cellpadding="3" width="100%" cellspacing="0" border="1" bordercolor="#000000">

                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Exclude Functions</strong></td></tr>
                <tr><td><?php foreach ($UdExcFuncArray as $key => $val) echo $key . ': ' . $val . '<br />'; ?></td></tr>

                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Exclude Constants</strong></td></tr>
                <tr><td><?php foreach ($UdExcConstArray as $key => $val) echo $key.': '. $val . '<br />'; ?></td></tr>

                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Functions returning objects (special handling)</strong></td></tr>
                <tr><td><?php foreach ($StdObjRetFunctionsArray as $key => $val) echo $key . ': ' . $val.'<br />'; ?></td></tr>

              </table>
            </td>

            <td width="20%" valign="top">
              <table cellpadding="3" width="100%" cellspacing="0" border="1" bordercolor="#000000">
                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Exclude Variables</strong></td></tr>
                <tr><td><?php foreach ($UdExcVarArray as $key => $val) echo $key . ': ' . $val . '<br />'; ?></td></tr>
                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Exclude Files</strong></td></tr>
                <tr><td><?php foreach ($UdExcFileArray as $key => $val) echo $key . ': ' . $val . '<br />'; ?></td></tr>
                <tr><td bgcolor="#E6E6E6" valign="top"><strong>Exclude Directories</strong></td></tr>
                <tr><td><?php foreach ($UdExcDirArray as $key => $val) echo $key . ': ' . $val . '<br />'; ?></td></tr>
              </table>
            </td>
          </tr>
        </table>

<?php

    }

}

?>