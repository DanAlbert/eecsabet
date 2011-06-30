<?php

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);
$assessed = $_POST['assessed'];
$mean = $_POST['mean'];
$median = $_POST['median'];
$high = $_POST['high'];
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

mysql_query('START TRANSACTION;', $con);

$size = sizeof($assessed);
$current = null;
for ($i = 0; $i < $size; $i++)
{
	$current = current($assessed);
	
	$cleanedAssessed = mysql_real_escape_string($assessed[key($assessed)]);
	
	$meanDecimalPos = strpos($mean[key($assessed)], '.');
	$medianDecimalPos = strpos($median[key($assessed)], '.');
	$highDecimalPos = strpos($high[key($assessed)], '.');
	$satisfactoryDecimalPos = strpos($satisfactory[key($assessed)], '.');
	
	$cleanedMean = '';
	if ($meanDecimalPos !== false)
	{
		$cleanedMean = preg_replace('/\D/', '', substr($mean[key($assessed)], 0, $meanDecimalPos));
	}
	else
	{
		$cleanedMean = preg_replace('/\D/', '', $mean[key($assessed)]);
	}
	
	$cleanedMedian = '';
	if ($medianDecimalPos !== false)
	{
		$cleanedMedian = preg_replace('/\D/', '', substr($median[key($assessed)], 0, $medianDecimalPos));
	}
	else
	{
		$cleanedMedian = preg_replace('/\D/', '', $median[key($assessed)]);
	}
	
	$cleanedHigh = '';
	if ($highDecimalPos !== false)
	{
		$cleanedHigh = preg_replace('/\D/', '', substr($high[key($assessed)], 0, $highDecimalPos));
	}
	else
	{
		$cleanedHigh = preg_replace('/\D/', '', $high[key($assessed)]);
	}
	
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
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 6);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}

	if ($cleanedMean == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 7);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}

	if ($cleanedMedian == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 8);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}

	if ($cleanedHigh == '')
	{
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 9);
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
		switch (submitComments($courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
		{
		case 0:
			onError($courseInstanceID, 11);
			break;
			
		case 1:
			onError($courseInstanceID, 10);
			break;
			
		case 2:
			onError($courseInstanceID, 5);
			break;
		}
		return;
	}
	
	$cloID = key($assessed);
	
	$query =	"UPDATE CourseInstanceCLO SET Assessed='$cleanedAssessed', " .
				"MeanScore='$cleanedMean', " .
				"MedianScore='$cleanedMedian', " .
				"HighScore='$cleanedHigh', " .
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

$query = "UPDATE CourseInstance SET State='Finalized' WHERE ID='$courseInstanceID';";
mysql_query($query, $con);
if (mysql_errno() != 0)
{
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	onError($courseInstanceID, 5);
	return;
}

switch (submitComments($con, $courseInstanceID, $prep, $prepActions, $changes, $clo, $recs))
{
case 0:
	mysql_query('COMMIT;', $con);
	mysql_close($con);
	onError($courseInstanceID, 12);
	break;
	
case 1:
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	onError($courseInstanceID, 14);
	break;
	
case 2:
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	onError($courseInstanceID, 5);
	break;
	
default:
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	onError($courseInstanceID, 15);
	break;
}

function submitComments($con, $courseInstanceID, $prep, $prepActions, $changes, $clo, $recs)
{
	if (($prep == '') OR ($prepActions == '') OR ($changes == '') OR ($clo == '') OR ($recs == ''))
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
		return 2;
	}
	
	return 0;
}

function onError($courseInstanceID, $errno)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=' . $errno);
	return;
}

?>
