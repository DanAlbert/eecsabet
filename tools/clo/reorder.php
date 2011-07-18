<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];

$numbers = $_POST['number'];

try
{
	$sth = $dbh->prepare(
		"SELECT * FROM CourseCLOInformation WHERE CourseID=:course");
	
	$sth->bindParam(':course', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$oldValues = array();
while ($row = $sth->fetch())
{
	$values = array();
	$values['CLONumber'] = $row->CLONumber;
	$values['Description'] = $row->Description;
	$values['Outcomes'] = $row->Outcomes;
	
	$oldValues[$row->CLOID] = $values;
}

$changed = array();
while ($num = current($numbers))
{
	if ($num != $oldValues[key($numbers)]['CLONumber'])
	{
		$changed[] = key($numbers);
	}
	next($numbers);
}

if (sizeof($changed) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=3');
}

// Begin
$dbh->beginTransaction();

$newIDs = array();

// Insert CLOs
try
{
	$sth = $dbh->prepare(
		"INSERT INTO CLO (CourseID, CLONumber, Description) " .
		"VALUES (:course, :number, :descr)");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':number', $cloNumber);
	$sth->bindParam(':descr', $description);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$outcomes = array();
$first = true;
foreach ($changed as $changedID)
{
	$cloNumber = $numbers[$changedID];
	$description = $oldValues[$changedID]['Description'];
	
	try
	{
		$sth->execute();
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		die('PDOException: ' . $e->getMessage());
	}	
	
	$newCLOID = $dbh->lastInsertId();
	$newIDs[] = $newCLOID;
	$outcomes[$newCLOID] = $oldValues[$changedID]['Outcomes'];
}

// Remove old MasterCLOs
try
{
	$sth = $dbh->prepare(
		"DELETE FROM MasterCLO WHERE CourseID=:course AND CLOID=:cloid");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':cloid', $changedID);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($changed as $changedID)
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

// Insert into MasterCLO
try
{
	$sth = $dbh->prepare(
		"INSERT INTO MasterCLO (CLOID, CourseID) VALUES (:cloid, :course)");
	
	$sth->bindParam(':cloid', $newID);
	$sth->bindParam(':course', $courseID);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($newIDs as $newID)
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

// Insert into CLOOutcomes
try
{
	$sth = $dbh->prepare("CALL AssociateOutcome(:cloid, :outcome, @result)");
	$sth->bindParam(':cloid', $newID);
	$sth->bindParam(':outcome', $char);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($newIDs as $newID)
{
	foreach (str_split($outcomes[$newID]) as $char)
	{
		if (ctype_alpha($char))
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
}

// Commit
$dbh->commit();

header('Location: index.php?courseID=' . $courseID);

?>
