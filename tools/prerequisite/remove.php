<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$remove = $_POST['remove'];

$toRemove = array();
while ($id = current($remove))
{
	if ($id == 'on')
	{
		$toRemove[] = key($remove);
	}
	next($remove);
}

if (sizeof($toRemove) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=2');
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

// Delete from Prerequisites
try
{
	$sth = $dbh->prepare(
		"DELETE FROM Prerequisites " .
		"WHERE CourseID=:course AND PrerequisiteID=:prereq");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':prereq', $prereq);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($toRemove as $prereq)
{
	try
	{
		$sth->execute();
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		die('PDOException: ' . $e->getMessage());
	}
}

// Delete from PrerequisiteAlternatives
try
{
	$sth = $dbh->prepare(
		"DELETE FROM PrerequisiteAlternatives " .
		"WHERE	 CourseID=:course AND " .
				"(PrerequisiteID=:prereq OR AlternativeID=:prereq)");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':prereq', $prereq);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($toRemove as $prereq)
{
	try
	{
		$sth->execute();
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		die('PDOException: ' . $e->getMessage());
	}
}

$dbh->commit();

header('Location: index.php?courseID=' . $courseID);

?>