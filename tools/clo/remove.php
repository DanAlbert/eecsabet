<?php

require_once '../../debug.php';
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

$query = "SELECT MIN(CLONumber) AS Min FROM CLO WHERE ";
$first = true;
foreach ($toRemove as $id)
{
	if (!$first)
	{
		$query .= ' OR ';
	}
	$query .= "ID='$id'";
	$first = false;
}
$query .= ';';

# VULNERABLE! FIX THIS!
$min = $dbh->query($query)->fetch()->Min;

// Begin
try
{
	$dbh->beginTransaction();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

// Delete from MasterCLO
try
{
	$sth = $dbh->prepare(
		"DELETE FROM MasterCLO WHERE CourseID=:course AND CLOID=:cloid");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':cloid', $cloid);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($toRemove as $cloid)
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

$newIDs = array();

try
{
	$sth = $dbh->prepare(
		"SELECT	 ID, " .
				"Description, " .
				"CLONumber, " .
				"GROUP_CONCAT(" .
					"DISTINCT CLOOutcomes.OutcomeID " .
					"SEPARATOR ',') AS Outcomes " .
		"FROM CLO, CLOOutcomes " .
		"WHERE	 CourseID=:course AND " .
				"CLOOutcomes.CLOID=CLO.ID AND " .
				"CLONumber>:min AND " .
				"CLO.ID IN (" .
					"SELECT CLOID " .
					"FROM MasterCLO " .
					"WHERE CourseID=:course) " .
		"GROUP BY CLO.ID " .
		"ORDER BY CLONumber ASC");
	
	$sth->bindParam(':course', $courseID);
	$sth->bindParam(':min', $min);

	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
if (sizeof($rows) > 0)
{
	$oldIDs = array();
	
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
	$cloNumber = $min;
	$first = true;
	foreach ($rows as $row)
	{
		$description = $row->Description;
	
		try
		{
			$sth->execute();
		}
		catch (PDOException $e)
		{
			$dbh->rollback();
			die('PDOException: ' . $e->getMessage());
		}
		
		$newCLOID = $dbh->lastInsertId();;
		$oldIDs[] = $row->ID;
		$newIDs[] = $newCLOID;
		
		$outcomes[$newCLOID] = $row->Outcomes;
		$cloNumber++;
	}
	
	// Remove old MasterCLOs
	try
	{
		$sth = $dbh->prepare(
			"DELETE FROM MasterCLO " .
			"WHERE CourseID=:course AND CLOID=:cloid");
		
		$sth->bindParam(':course', $courseID);
		$sth->bindParam(':cloid', $oldID);
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	foreach ($oldIDs as $oldID)
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
		$sth = $dbh->prepare(
			"INSERT INTO CLOOutcomes (CLOID, OutcomeID) " .
			"VALUES (:cloid, :outcome)");
		
		$sth->bindParam(':cloid', $newID);
		$sth->bindParam(':outcome', $outcome);
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	foreach ($newIDs as $newID)
	{
		foreach (explode(',', $outcomes[$newID]) as $outcome)
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