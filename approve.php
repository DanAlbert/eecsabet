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
$recs = mysql_real_escape_string($_POST['recs']);

if (sizeof($assessed) == 0)
{
	onError($courseInstanceID, 3);
	return;
}

mysql_query('START TRANSACTION;', $con);

$size = sizeof($assessed);
$current = null;
for ($i = 0; $i < $size; $i++)
{
	$current = current($assessed);
	
	$cleanedAssessed = mysql_real_escape_string($assessed[key($assessed)]);
	$satisfactoryDecimalPos = strpos($satisfactory[key($assessed)], '.');
	
	$cleanedSatisfactory = '';
	if ($satisfactoryDecimalPos !== false)
	{
		$cleanedSatisfactory = preg_replace('/\D/', '', substr($satisfactory[key($assessed)], 0, $satisfactoryDecimalPos));
	}
	else
	{
		$cleanedSatisfactory = preg_replace('/\D/', '', $satisfactory[key($assessed)]);
	}
	
	if (($cleanedAssessed == '') OR ($cleanedAssessed == null))
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $recs))
		{
		case 0:
			onError($courseInstanceID, 13);
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

	if ($cleanedSatisfactory == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $recs))
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
	
	$cloID = key($assessed);
	
	$query =	"UPDATE CourseInstanceCLO SET Assessed='$cleanedAssessed', " .
				"SatisfactoryScore='$cleanedSatisfactory' " .
				"WHERE CLOID='$cloID' AND CourseInstanceID='$courseInstanceID';";
	
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

switch (submitComments($courseInstanceID, $recs))
{
case 0:
	onError($courseInstanceID, 12);
	break;
	
case 1:
	onError($courseInstanceID, 13);
	break;
	
case 2:
	onError($courseInstanceID, 5);
	break;
}

return;

function submitComments($courseInstanceID, $recs)
{
	$con = dbConnect();
	if (!con)
	{
		die('Unable to connect to database: ' . mysql_error());
	}
	
	if ($recs == '')
	{
		return 1;
	}
	
	$query = "UPDATE CourseInstance SET CommentRecs='$recs' WHERE ID='$courseInstanceID';";
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
