<?php

function submitInitialComments($dbh, $courseInstanceID, $recs)
{
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

function submitFinalComments(
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

?>
