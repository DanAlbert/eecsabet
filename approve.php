<?php

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);
$assessed = mysql_real_escape_string($_POST['assessed']);
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

	$query = "UPDATE CourseInstanceCLO SET Assessed='" . $assessed[$i] . "', SatisfactoryScore='" . $satisfactory[$i] . "' WHERE CLOID='$i' AND CourseInstanceID='$courseInstanceID';";
	//print $query . '<br />';
	
	mysql_query($query, $con);
	if (mysql_errno() != 0)
	{
		mysql_query('ROLLBACK;', $con);
		header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
		return;
	}
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
