<?php

include_once 'debug.php';
require_once 'db.php';

$dbh = dbConnect();

$courseInstanceID = $_REQUEST['courseInstanceID'];
$assessed = $_POST['assessed'];
$mean = $_POST['mean'];
$median = $_POST['median'];
$high = $_POST['high'];
$satisfactory = $_POST['satisfactory'];

$prep = $_POST['prep'];
$prepActions = $_POST['prepActions'];
$changes = $_POST['changes'];
$clo = $_POST['clo'];
$recs = $_POST['recs'];

if (sizeof($assessed) == 0)
{
	onError($courseInstanceID, 3);
	return;
}

try
{
	$dbh->beginTransaction();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getmessage());
}

$size = sizeof($assessed);
$current = null;
for ($i = 0; $i < $size; $i++)
{
	$current = current($assessed);
	
	$cleanedAssessed = $assessed[key($assessed)];
	
	$meanDecimalPos = strpos($mean[key($assessed)], '.');
	$medianDecimalPos = strpos($median[key($assessed)], '.');
	$highDecimalPos = strpos($high[key($assessed)], '.');
	$satisfactoryDecimalPos = strpos($satisfactory[key($assessed)], '.');
	
	$cleanedMean = '';
	if ($meanDecimalPos !== false)
	{
		$intPart = substr($mean[key($assessed)], 0, $meanDecimalPos);
		$cleanedMean = preg_replace('/\D/', '', $intPart);
	}
	else
	{
		$cleanedMean = preg_replace('/\D/', '', $mean[key($assessed)]);
	}
	
	$cleanedMedian = '';
	if ($medianDecimalPos !== false)
	{
		$intPart = substr($median[key($assessed)], 0, $medianDecimalPos);
		$cleanedMedian = preg_replace('/\D/', '', $intPart);
	}
	else
	{
		$cleanedMedian = preg_replace('/\D/', '', $median[key($assessed)]);
	}
	
	$cleanedHigh = '';
	if ($highDecimalPos !== false)
	{
		$intPart = substr($high[key($assessed)], 0, $highDecimalPos);
		$cleanedHigh = preg_replace('/\D/', '', $intPart);
	}
	else
	{
		$cleanedHigh = preg_replace('/\D/', '', $high[key($assessed)]);
	}
	
	$cleanedSatisfactory = '';
	if ($satisfactoryDecimalPos !== false)
	{
		$intPart =
			substr($satisfactory[key($assessed)], 0, $satisfactoryDecimalPos);
		
		$cleanedSatisfactory = preg_replace('/\D/', '', $intPart);
	}
	else
	{
		$cleanedSatisfactory =
			preg_replace('/\D/', '', $satisfactory[key($assessed)]);
	}
	
	if (($cleanedAssessed == '') OR ($cleanedAssessed == null))
	{
		$dbh->rollback();
		
		switch (submitComments(	$dbh,
								$courseInstanceID,
								$prep, $prepActions,
								$changes,
								$clo,
								$recs))
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
		$dbh->rollback();
		
		switch (submitComments(	$dbh,
								$courseInstanceID,
								$prep,
								$prepActions,
								$changes,
								$clo,
								$recs))
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
		$dbh->rollback();
		
		switch (submitComments(	$dbh,
								$courseInstanceID,
								$prep,
								$prepActions,
								$changes,
								$clo,
								$recs))
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
		$dbh->rollback();
		
		switch (submitComments(	$dbh,
								$courseInstanceID,
								$prep,
								$prepActions,
								$changes,
								$clo,
								$recs))
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
		$dbh->rollback();
		
		switch (submitComments(	$dbh,
								$courseInstanceID,
								$prep,
								$prepActions,
								$changes,
								$clo,
								$recs))
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
	
	try
	{
		$sth = $dbh->prepare(
			"UPDATE CourseInstanceCLO " .
			"SET Assessed=:assessed, " .
				"MeanScore=:mean, " .
				"MedianScore=:median, " .
				"HighScore=:high, " .
				"SatisfactoryScore=:satisfactory " .
			"WHERE CLOID=:cloid AND CourseInstanceID=:id");
		
		$sth->bindParam(':assessed', $cleanedAssessed);
		$sth->bindParam(':mean', $cleanedMean);
		$sth->bindParam(':median', $cleanedMedian);
		$sth->bindParam(':high', $cleanedHigh);
		$sth->bindParam(':satisfactory', $cleanedSatisfactory);
		$sth->bindParam(':cloid', $cloID);
		$sth->bindparam(':id', $courseInstanceID);
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		onError($courseInstanceID, 5);
		return;
	}
	
	next($assessed);
}

try
{
	$sth = $dbh->prepare(
		"UPDATE CourseInstance " .
		"SET State='Finalized' " .
		"WHERE ID=:id");
	
	$sth->bindParam(':id', $courseInstanceID);
	$sth->execute();
}
catch (PDOException $e)
{
	$dbh->rollback();
	onError($courseInstanceID, 5);
	return;
}

switch (submitComments(	$dbh,
						$courseInstanceID,
						$prep,
						$prepActions,
						$changes,
						$clo,
						$recs))
{
case 0:
	$dbh->commit();
	onError($courseInstanceID, 12);
	break;
	
case 1:
	$dbh->rollback();
	onError($courseInstanceID, 14);
	break;
	
case 2:
	$dbh->rollback();
	onError($courseInstanceID, 5);
	break;
	
default:
	$dbh->rollback();
	onError($courseInstanceID, 15);
	break;
}

function submitComments(
	$dbh,
	$courseInstanceID,
	$prep,
	$prepActions,
	$changes,
	$clo,
	$recs)
{
	if (($prep == '') OR
		($prepActions == '') OR
		($changes == '') OR
		($clo == '') OR
		($recs == ''))
	{
		return 1;
	}
	
	try
	{
		$sth = $dbh->prepare(
			"UPDATE CourseInstance SET " .
			"CommentPrep=:prep, " .
			"CommentPrepActions=:prepActions, " .
			"CommentChanges=:changes, " .
			"CommentCLO=:clo, " .
			"CommentRecs=:recs " .
			"WHERE ID=:id");
		
		$sth->bindParam(':prep', $prep);
		$sth->bindParam(':prepActions', $prepActions);
		$sth->bindParam(':changes', $changes);
		$sth->bindParam(':clo', $clo);
		$sth->bindParam(':recs', $recs);
		$sth->bindParam(':id', $courseInstanceID);
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die($e->getMessage());
		return 2;
	}
	
	return 0;
}

function onError($courseInstanceID, $errno)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID .
		'&error=' . $errno);
}

?>
