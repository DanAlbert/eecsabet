<?php

$hostname = '';
$username = 'eecsabet';
$password = '';
$database = 'eecsabet';

/*$con = mysql_connect($hostname, $username, $password);
if (!$con)
{
	mysql_close($con);
	die('Unable to connect to database: ' . mysql_error());
}

if (!mysql_select_db($database))
{
	mysql_close($con);
	die('Unable to select database: ' . mysql_error());
}*/

$courseID = $_REQUEST['courseID'];
$outcomeID = $_REQUEST['outcomeID'];
$assessed = $_POST['assessed'];
$mean = $_POST['mean'];
$median = $_POST['median'];
$high = $_POST['high'];
$satisfactory = $_POST['satisfactory'];

if ($assessed == '')
{
	//mysql_close($con);
	header('Location: index.php?courseID=' . $courseID . '&error=3');
	return;
}

if ($satisfactory == '')
{
	//mysql_close($con);
	header('Location: index.php?courseID=' . $courseID . '&error=4');
	return;
}

print "Approving Course $courseID Outcome $outcomeID.<br />How/Where Assessed: $assessed<br />Mean Score: $mean<br />Median Score: $median<br />High Score: $high<br />Satisfactory Score: $satisfactory";

//mysql_close($con);
?>
