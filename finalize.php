<?php

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);
$assessed = mysql_real_escape_string($_POST['assessed']);
$mean = mysql_real_escape_string($_POST['mean']);
$median = mysql_real_escape_string($_POST['median']);
$high = mysql_real_escape_string($_POST['high']);
$satisfactory = mysql_real_escape_string($_POST['satisfactory']);

mysql_query('BEGIN TRANSACTION;', $con);
while ($current = current($assessed))
{
	if ($assessed[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=6');
		return;
	}

	if ($mean[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=7');
		return;
	}

	if ($median[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=8');
		return;
	}

	if ($high[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=9');
		return;
	}

	if ($satisfactory[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=10');
		return;
	}
	
	$query =	"UPDATE CourseInstanceCLO SET Assessed='" . $assessed[key($assessed)] .
				"', MeanScore='" . $mean[key($assessed)] .
				"', MedianScore='" . $median[key($assessed)] .
				"', HighScore='" . $high[key($assessed)] .
				"', SatisfactoryScore='" . $satisfactory[key($assessed)] .
				"' WHERE CLOID='" . key($assessed) . "' AND CourseInstanceID='$courseInstanceID';";
	
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
