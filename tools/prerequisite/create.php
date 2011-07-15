<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$prerequisiteID = $_POST['prerequisiteID'];

$isCoreq = 0;
if (isset($_POST['isCorequisite']) AND ($_POST['isCorequisite'] == 'on'))
{
	$isCoreq = 1;
}

try
{
	$dbh->beginTransaction();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

// Insert into Prerequisites
try
{
	$sth = $dbh->prepare(
		"INSERT INTO Prerequisites (CourseID, PrerequisiteID, IsCorequisite) " .
		"VALUES (:course, :prereq, :coreq)");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':prereq', $prerequisiteID);
	$sth->bindParam(':coreq', $isCoreq);
	
	$sth->execute();
}
catch (PDOException $e)
{
	$dbh->rollback();
	die('PDOException: ' . $e->getMessage());
}

if (isset($_POST['alt']))
{
	$alts = $_POST['alt'];
	
	// Insert into PrerequisiteAlternatives
	try
	{
		$sth = $dbh->prepare(
			"INSERT INTO PrerequisiteAlternatives " .
			"(CourseID, PrerequisiteID, AlternativeID) " .
			"VALUES (:course, :prereq, :alt), (:course, :alt, :prereq)");
		
		$sth->bindParam(':course', $courseID);
		$sth->bindParam(':prereq', $prerequisiteID);
		$sth->bindParam(':alt', $alt);
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		die('PDOException: ' . $e->getMessage());
	}
	
	foreach ($alts as $alt)
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
}

$dbh->commit();

header('Location: index.php?courseID=' . $courseID);

?>