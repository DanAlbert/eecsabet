<?php

$hostname = 'mysql.gingerhq.net';
$username = 'eecsabet';
$password = 'hP5fRjZbZ6KcL7MU';
$database = 'eecsabet';

$con = mysql_connect($hostname, $username, $password);
if (!$con)
{
	mysql_close($con);
	die('Unable to connect to database: ' . mysql_error());
}

if (!mysql_select_db($database))
{
	mysql_close($con);
	die('Unable to select database: ' . mysql_error());
}

$courseInstanceID = $_REQUEST['courseInstanceID'];
$outcomeID = $_REQUEST['outcomeID'];
$assessed = $_POST['assessed'];
$mean = $_POST['mean'];
$median = $_POST['median'];
$high = $_POST['high'];
$satisfactory = $_POST['satisfactory'];

if ($assessed == '')
{
	mysql_close($con);
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=3');
	return;
}

if ($satisfactory == '')
{
	mysql_close($con);
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=4');
	return;
}

$query = "UPDATE CourseInstanceCLO SET Assessed='$assessed', MeanScore='$mean', MedianScore='$median', HighScore='$high', SatisfactoryScore='$satisfactory', State='Finalized' WHERE CLOID='$outcomeID' AND CourseInstanceID='$courseInstanceID';";
mysql_query($query, $con);
if (mysql_errno() == 0)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=0');
}
else
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
}

mysql_close($con);
?>
