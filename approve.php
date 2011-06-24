<?php

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);
$assessed = $_POST['assessed'];
$satisfactory = $_POST['satisfactory'];

mysql_query('BEGIN TRANSACTION;', $con);
while ($current = current($assessed))
{
	if ($assessed[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=3');
		return;
	}

	if ($satisfactory[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=4');
		return;
	}

	$query = "UPDATE CourseInstanceCLO SET Assessed='" . $assessed[key($assessed)] . "', SatisfactoryScore='" . $satisfactory[key($assessed)] . "' WHERE CLOID='" . key($assessed) . "' AND CourseInstanceID='$courseInstanceID';";
	
	mysql_query($query, $con);
	if (mysql_errno() != 0)
	{
		mysql_query('ROLLBACK;', $con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
		return;
	}
	
	next($assessed);
}

$query = "UPDATE CourseInstance SET State='Approved' WHERE ID='$courseInstanceID';";
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
