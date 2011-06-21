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

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);
$assessed = mysql_real_escape_string($_POST['assessed']);
$mean = mysql_real_escape_string($_POST['mean']);
$median = mysql_real_escape_string($_POST['median']);
$high = mysql_real_escape_string($_POST['high']);
$satisfactory = mysql_real_escape_string($_POST['satisfactory']);

mysql_query('BEGIN TRANSACTION;', $con);
for ($i = 1; $i <= sizeof($assessed); $i++)
{
	if ($assessed == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=3');
		return;
	}

	if ($satisfactory == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=4');
		return;
	}

	//$query = "UPDATE CourseInstanceCLO SET Assessed='$assessed', MeanScore='$mean', MedianScore='$median', HighScore='$high', SatisfactoryScore='$satisfactory', State='Finalized' WHERE CLOID='$outcomeID' AND CourseInstanceID='$courseInstanceID';";
	$query =	"UPDATE CourseInstanceCLO SET Assessed='" . $assessed[$i] .
				"', MeanScore='" . $mean[$i] .
				"', MedianScore='" . $median[$i] .
				"', HighScore='" . $high[$i] .
				"', SatisfactoryScore='" . $satisfactory[$i] .
				"' WHERE CLOID='$i' AND CourseInstanceID='$courseInstanceID';";
	
	mysql_query($query, $con);
	if (mysql_errno() != 0)
	{
		mysql_query('ROLLBACK;', $con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
		return;
	}
}

$query = "UPDATE CourseInstance SET State='Finalized' WHERE ID='$courseInstanceID';";
mysql_query($query, $con);
if (mysql_errno() != 0)
{
	mysql_query('ROLLBACK;', $con);
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
	return;
}

mysql_query('COMMIT;', $con);
mysql_close($con);

header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=0');

?>
