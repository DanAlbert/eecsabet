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

$prep = mysql_real_escape_string($_POST['prep']);
$prepActions = mysql_real_escape_string($_POST['prepActions']);
$changes = mysql_real_escape_string($_POST['changes']);
$clo = mysql_real_escape_string($_POST['clo']);
$recs = mysql_real_escape_string($_POST['recs']);

if (sizeof($assessed) == 0)
{
	onError($courseInstanceID, 3);
	return;
}

mysql_query('BEGIN TRANSACTION;', $con);

$size = sizeof($assessed);
$current = null;
for ($i = 0; $i < $size; $i++)
{
	$current = current($assessed);
	if (($assessed[key($assessed)] == '') OR ($assessed[key($assessed)] == null))
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 3);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}

	if ($satisfactory[key($assessed)] == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 4);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}

	$query = "UPDATE CourseInstanceCLO SET Assessed='" . $assessed[key($assessed)] . "', SatisfactoryScore='" . $satisfactory[key($assessed)] . "' WHERE CLOID='" . key($assessed) . "' AND CourseInstanceID='$courseInstanceID';";
	
	mysql_query($query, $con);
	if (mysql_errno() != 0)
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		onError($courseInstanceID, 5);
		return;
	}
	
	next($assessed);
}

$query = "UPDATE CourseInstance SET State='Approved' WHERE ID='$courseInstanceID';";
mysql_query($query, $con);
if (mysql_errno() != 0)
{
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	onError($courseInstanceID, 5);
	return;
}

mysql_query('COMMIT;', $con);
mysql_close($con);

switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
{
case 0:
	onError($courseInstanceID, 12);
	break;
	
case 1:
	onError($courseInstanceID, 0);
	break;
	
case 2:
	onError($courseInstanceID, 5);
	break;
}

return;

function submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs)
{
	$con = dbConnect();
	if (!con)
	{
		die('Unable to connect to database: ' . mysql_error());
	}
	
	if (($prep == '') AND ($prepActions == '') AND ($changes == '') AND ($clo == '') AND ($recs == ''))
	{
		return 1;
	}
	
	$query =	"UPDATE CourseInstance SET " .
				"CommentPrep='$prep', " .
				"CommentPrepActions='$prepActions', " .
				"CommentChanges='$changes', " .
				"CommentCLO='$clo', " .
				"CommentRecs='$recs' " .
				"WHERE ID='$courseInstanceID';";
	mysql_query($query, $con);
	if (mysql_errno() != 0)
	{
		mysql_close($con);
		return 2;
	}

	mysql_close($con);
	return 0;
}

function onError($courseInstanceID, $errno)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=' . $errno);
	return;
}

?>
