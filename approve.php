<?php

include_once 'debug.php';
require_once 'db.php';

$dbh = dbConnect();

$courseInstanceID = $_REQUEST['courseInstanceID'];
$assessed = $_POST['assessed'];
$satisfactory = $_POST['satisfactory'];
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
	die('PDOException: ' . $e->getMessage());
}

$size = sizeof($assessed);
$current = null;
for ($i = 0; $i < $size; $i++)
{
	$current = current($assessed);
	
	$cleanedAssessed = $assessed[key($assessed)];
	$satisfactoryDecimalPos = strpos($satisfactory[key($assessed)], '.');
	
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
		$dbh->rollback();
		
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
	
	try
	{
		$sth = $dbh->prepare(
			"UPDATE CourseInstanceCLO " .
			"SET Assessed=:assessed, " .
			"SatisfactoryScore=:satisfactory " .
			"WHERE CLOID=:cloid AND CourseInstanceID=:id");
		
		$sth->bindParam(':assessed', $cleanedAssessed);
		$sth->bindParam(':satisfactory', $cleanedSatisfactory);
		$sth->bindParam(':cloid', $cloID);
		$sth->bindParam(':id', $courseInstanceID);
		
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
		"SET State='Approved' " .
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

$dbh->commit();

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
	$dbh = dbConnect();
	
	if ($recs == '')
	{
		return 1;
	}
	
	try
	{
		$sth = $dbh->prepare(
			"UPDATE CourseInstance " .
			"SET CommentRecs=:recs " .
			"WHERE ID=:id");
		
		$sth->bindParam(':recs', $recs);
		$sth->bindParam(':id', $courseInstanceID);
		
		$sth->execute();
	}
	catch (PDOException $e)
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
