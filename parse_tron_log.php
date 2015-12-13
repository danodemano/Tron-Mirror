<?php
//Database configurations
$dbhost = 'localhost';
$dbuser = 'tron';
$dbpass = 'tron';
$dbname = 'tron';

//Connect to the MySQL server
$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysqli_select_db($conn, $dbname);

//Get the last date/time from the control table
$sql = "SELECT `value` FROM `control` WHERE `field`='last_non_ssl_log'";
$res = mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
$row = mysqli_fetch_assoc($res);
$non_ssl_log = $row['value'];

$sql = "SELECT `value` FROM `control` WHERE `field`='last_ssl_log'";
$res = mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
$row = mysqli_fetch_assoc($res);
$ssl_log = $row['value'];

//Set initial values for the date/time variables
$new_ssl_log_date = "0000-00-00 00:00:00";
$new_non_ssl_log_date = "0000-00-00 00:00:00";

//The tron log file we are going to parse out
$handle = fopen("/var/log/httpd/tron_ssl_access_log", "r");
if ($handle) {
	//Loop through all the lines in the file
    while (($line = fgets($handle)) !== false) {
		//Parse out each line into an array
		if (preg_match("/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\") (\S+) (\S+)$/", $line, $m)) {
			//Fix the date to a proper format and add in the time
			$date = explode("/", $m[4]);
			$month = date('m', strtotime($date[1]));
			$new_date = $date[2] . "-" . $month . "-" . $date[0] . " " . $m[5];
			//Check if the date/time is newer than our last check, if we don't have a 404, and if we are an exe	
			if (($new_date > $ssl_log) AND ($m[10] <> "404") AND ($m[11] > 1) AND (strstr($m[8], ".exe") == ".exe")) {
				//This record is a valid download - insert it into the database
				//Get the version from the download string
				$version = explode (" ", urldecode($m[8]));
				$sql = "INSERT INTO `downloads` (`date`, `time`, `size`, `version`, `secure`) VALUES ('".mysqli_real_escape_string($conn, $new_date)."', '".
				mysqli_real_escape_string($conn, $m[5])."', '".mysqli_real_escape_string($conn, $m[15])."', '".mysqli_real_escape_string($conn, $version[1])."', '1');";
				mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
			} //end if (($new_date > $ssl_log) AND ($m[10] <> "404") AND ($m[11] > 1) AND (strstr($m[8], ".exe") == ".exe")) {
			//Check if the new date from the log is greater than the last date
			if ($new_date > $new_ssl_log_date) {
				$new_ssl_log_date = $new_date;
			} //end if ($new_date > $new_ssl_log_date) {
			//print_r ($m);
		}
    }
	
	//Make sure that the new date isn't the default
	if ($new_ssl_log_date <> "0000-00-00 00:00:00") {
		//Update the control table with the new date/time
		$sql = "UPDATE `control` SET `value` = '$new_ssl_log_date' WHERE `field` = 'last_ssl_log';";
		mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
	} //end if ($new_ssl_log_date <> "0000-00-00 00:00:00") {

    fclose($handle);
} else {
    // error opening the file.
} 

//Repeat with the non-ssl log file
$handle = fopen("/var/log/httpd/tron_access_log", "r");
if ($handle) {
	//Loop through all the lines in the file
    while (($line = fgets($handle)) !== false) {
		//Parse out each line into an array
		if (preg_match("/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\") (\S+) (\S+)$/", $line, $m)) {
			//Fix the date to a proper format
			$date = explode("/", $m[4]);
			$month = date('m', strtotime($date[1]));
			$new_date = $date[2] . "-" . $month . "-" . $date[0] . " " . $m[5];
			//Check if the date/time is newer than our last check, if we don't have a 404, and if we are an exe	
			if (($new_date > $non_ssl_log) AND ($m[10] <> "404") AND ($m[11] > 1) AND (strstr($m[8], ".exe") == ".exe")) {
				//This record is a valid download - insert it into the database
				//Get the version from the download string
				$version = explode (" ", urldecode($m[8]));
				$sql = "INSERT INTO `downloads` (`date`, `time`, `size`, `version`, `secure`) VALUES ('".mysqli_real_escape_string($conn, $new_date)."', '".
				mysqli_real_escape_string($conn, $m[5])."', '".mysqli_real_escape_string($conn, $m[15])."', '".mysqli_real_escape_string($conn, $version[1])."', '0');";
				mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
			} //end if (($new_date > $non_ssl_log) AND ($m[10] <> "404") AND ($m[11] > 1) AND (strstr($m[8], ".exe") == ".exe")) {
			//Check if the new date from the log is greater than the last date
			if ($new_date > $new_non_ssl_log_date) {
				$new_non_ssl_log_date = $new_date;
			} //end if ($new_date > $new_ssl_log_date) {
			//print_r ($m);
		}
    }
	
	//Make sure that the new date isn't the default
	if ($new_non_ssl_log_date <> "0000-00-00 00:00:00") {
		//Update the control table with the new date/time
		$sql = "UPDATE `control` SET `value` = '$new_non_ssl_log_date' WHERE `field` = 'last_non_ssl_log';";
		mysqli_query($conn, $sql) or die('Error, query failed. ' . mysqli_error($conn) . '<br>Line: '.__LINE__ .'<br>File: '.__FILE__);
	} //end if ($new_ssl_log_date <> "0000-00-00 00:00:00") {

    fclose($handle);
} else {
    // error opening the file.
} 

//Close the database connection
mysqli_close($conn);

echo ("Done!\r\n");
