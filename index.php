<?php
/*
***************************************************************
***************************************************************
Created by:			Dan Bunyard
Email:					danodemano@gmail.com
Created on:			02/12/2015
Last modified on:	11/07/2015
***************************************************************
Files:					index.php
***************************************************************
Description:			This script is used to display the Tron
download link and also check for a current version against
the official mirror.
***************************************************************
Notes:					Combine this with the auto-update script
for an ideal mirror setup.  With this page plus the auto-update
script your mirror is virtually hands-off.
***************************************************************
Revisions: 			20151010 - Updated to use sha256 sum file
							20151102 - Added socket timeout
							20151106 - Update style
							20151107 - Replaced tables with CSS and cleanup
							20151123 - Fixed URL encoding
							20160212 - Update checks now uses hashes also
***************************************************************
To do list: 				Add download tracking
***************************************************************
***************************************************************
The MIT License (MIT)

Copyright (c) 2015 Daniel Bunyard

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

//Global variables, change as needed
$official_sha256    	= 'https://bmrf.org/repos/tron/sha256sums.txt'; //The path the the official sha256sum text file
$official_mirror 		= 'https://bmrf.org/repos/tron/'; //Same as above, just the root URL
$author_bitcoin  	= '1LSJ9qDzuHyRx6FfbUmHVSii4sLU3sx2TF'; //The script author bitcoin address
$mirror_bitcoin  	= '1NpofcZqWNWHamcYhdk9kyxKdzrSSi42cL'; //The mirror ops bitcoin address
$mirror_email    	= 'webmaster@danodemano.com'; //The email address of this mirror op to display for a contact point
$tron_wiki	 	 		= 'https://www.reddit.com/r/TronScript/wiki/index'; //This is the wiki link to be show if the visitor wants more info
$mirror_provider 	= '/u/Danodemano'; //This is the mirror provider to show in the page title
$update_script	 	= 'tronupdate.sh'; //The name of the auto-update script for download link
$check_timeout 	= 5; //In seconds - Leave this as-is unless you have issues with timeouts to the official repo

/*************************************************************************************************************************************
**************************************************************************************************************************************
**************************************************************************************************************************************
WARNING WARNING WARNING - UNLESS YOU KNOW WHAT YOU ARE DOING DO NOT EDIT BELOW THIS LINE - WARNING WARNING WARNING
**************************************************************************************************************************************
**************************************************************************************************************************************
*************************************************************************************************************************************/

//Need to make sure that errors communicating with the official mirror don't cause this page to hang!
ini_set('default_socket_timeout', $check_timeout); //Timeout in seconds from the variable above

//Locating the exe file and version
$files = array();
$current_path = realpath(dirname(__FILE__));

//Search for .exe files in this directory
foreach (glob(realpath(dirname(__FILE__)) . "/Tron*.exe") as $file) {
	$files[] = basename($file);
} //end foreach (glob(realpath(dirname(__FILE__)) . "/*.exe") as $file) {

//Get the mirror file from the array
$array_size = count($files);
$mirror_file = $files[$array_size - 1];
$tmp_array = array();
$tmp_array = explode(" ", $mirror_file);
$mirror_version = $tmp_array[1];
unset($tmp_array);

//Get the current local hash
$data = file("sha256sums.txt");
$line = $data[count($data)-1];
$tmp_array = array();
$tmp_array = explode(",", $line);
$mirror_hash = $tmp_array[1];
unset($data);
unset($line);
unset($tmp_array);

//Get the latest version by parsing out the text file from the official mirror
$file = $official_sha256;
$data = file($file);
$line = $data[count($data)-1];
$tmp_array = array();
$tmp_array = explode(" ", $line);
$tmp_array2 = explode(",", $tmp_array[0]);
$latest_version = $tmp_array[1];
$latest_hash = $tmp_array2[1];
unset($data);
unset($line);
unset($tmp_array);
unset($tmp_array2);

//Get the file last modified time
$modified_time = date ('Y-m-d H:i:s', filectime($mirror_file));
$gmt_modified = gmdate('F d Y H:i:s', strtotime($modified_time));

//Get the filesize of the Tron EXE
$filesize = round((((filesize($mirror_file)) / 1024) / 1024), 0);

//Get the current running script name
$script_name = $_SERVER['SCRIPT_NAME'];
$break = Explode('/', $script_name);
$script_name = $break[count($break) - 1];

?>
<!DOCTYPE html>
<html>
<head>
<title>Tron Script Mirror Provided by <?php echo $mirror_provider; ?></title>
<style type="text/css">
body {
	font-family: 'Orbitron', sans-serif;
	background-color: #88898A;
	margin: 50px;
}
.box {
	width: 600px;
	padding: 10px;
	background-color: #fff;
	margin: 0 auto;
	font-family: Arial;
	-moz-border-radius: 15px;
	border-radius: 15px;
	margin-top: 5px;
	text-align: center
}
.header {
	font-family: 'Orbitron', sans-serif;
	font-size: 24pt;
	text-align: center;
}
.download {
	display: block;
	border-radius: 3px;
	width: 78%;
	background: linear-gradient(#87D37C, #6C826E);
	height: 45px;
	border: 1px solid black;
	padding: 10px;
	font-family: Arial;
	color: white;
	text-shadow: 0px 1px 0px black;
	font-weight: bold;
	margin: 0 auto;
	text-align: center;
	box-shadow: 0px 0px 1px black;
	cursor: hand;
	font-size: 20pt;
	line-height: 22px;
}
a.download {
    text-decoration: none;
}
.small {
	font-family: Arial;
	font-size: 8pt;
	text-align: center;
}
.footer {
	width: 600px;
//	background-color: rgba(0, 0, 0, 0.2);
	padding: 20px;
	color: #c9c9c9;
	margin: 10px auto;
	text-align: center;
	font-size: 10pt;
	font-family: Arial;
}
.current  {
	font-family: 'Orbitron', sans-serif;
    color:green;
	text-align: center;
    font-size:140%;
    font-weight: bold;
}
.outdated  {
	font-family: 'Orbitron', sans-serif;
    color:red;
	text-align: center;
    font-size:120%;
    font-weight: bold;
}
.error  {
	font-family: 'Orbitron', sans-serif;
    color:red;
	text-align: center;
    font-size:100%;
    font-weight: bold;
}
.versions {
	font-family: Arial;
	font-size: 10pt;
	text-align: center;
}
.dlsize {
	font-family: Arial;
	font-size: 12px;
}
.dltable {
	font-family: Arial;
	text-align: center;
}
A.reddit:link {
	font-family: Arial;
	text-decoration: none;
}
.partialbreak {
	font-size: 2px;
}
</style>
<!--Make sure we included the needed font-->
<link href='https://fonts.googleapis.com/css?family=Orbitron:900' rel='stylesheet' type='text/css'>
</head>
<body>

<div class="box">

<div class="header">
Tron Script Mirror
</div>

<?php
//Temp override until official sha256sums file gets fixed
//$latest_version="v8.4.0";

//Compare the mirror version against the official version
//Also let the visitor know if this mirror is current
if (($latest_version === $mirror_version) AND ($latest_hash === $mirror_hash)) {
	//This mirror is current
	echo '<div class="current">This mirror has the current version!</div>'."\r\n";
} else if (empty($latest_version)) {
	//Problem communicating with the official mirror.
	echo '<div class="current">This mirror probably has the current version.</div><br>'."\r\n".
		     '<div class="error">There was an error communicating with the official repo.</div>'."\r\n";
} else {
	//This mirror is not current
	echo '<div class="outdated">This mirror has an outdated version!</div>'."\r\n";
} //end if ($latest_version === $mirror_version) {
?>
<br>
<div class="dltable"><div class="small">
<a href="<?php echo(rawurlencode($mirror_file)); ?>" class="download">Download Tron <?php echo $mirror_version; ?>
<br>
<span class="dlsize"><?php echo $filesize;?>&nbsp;MB</span></a><br>
<a href="sha256sums.txt">sha256</a> | <a href="sha256sums.txt.asc">sha256 signature</a>
</div>
</div>
</div>
<div class="box">
Tron is a script that "fights for the User"; basically automates a bunch of scanning/disinfection/cleanup tools on a Windows system.<br>
For more information, <a href="<?php echo $tron_wiki; ?>" target="_blank">click here</a>.
</div>
<div class="box">
If you encounter problems with this mirror please contact: <a href="mailto:<?php echo $mirror_email; ?>"><?php echo $mirror_email; ?></a>.<br><br>
<div class="versions">Official Mirror Version: <b><?php if (!empty($latest_version)) {echo $latest_version; }else{ echo '<span style="color: red;"><b>Unknown</b></span>'; } ?>
</b>&nbsp;|&nbsp;This Mirror Version: <b><?php echo $mirror_version; ?></b><br>
Last updated: <?php echo $gmt_modified?> (GMT/UTC) </div>
<br>
<div class="small"><a href="showsource.php?file=<?php echo $script_name; ?>">View Source</a>&nbsp;|&nbsp;<a href="<?php echo $update_script; ?>">Tron Mirror Auto-Update Script</a></div>
</div>
<div class="footer">Tron Script Created by <a href="https://www.reddit.com/user/vocatus" target="_blank" class="reddit">/u/vocatus</a>&nbsp;-&nbsp;
<a href="bitcoin:<?php echo $author_bitcoin; ?>">Send him bitcoins!</a></div>
</body>
</html>
