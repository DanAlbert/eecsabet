<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
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
}
$query .= ';';

$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$min = $row['Min'];

// Begin
mysql_query('START TRANSACTION;', $con);

// Delete from MasterCLO
$query = "DELETE FROM MasterCLO WHERE CourseID='$courseID' AND (";
$first = true;
foreach ($toRemove as $id)
{
	if (!$first)
	{
		$query .= " OR ";
	}
	$query .= "CLOID='$id'";
	$first = false;
}
$query .= ');';

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Find the highest CLO ID
$query = "SELECT MAX(CLO.ID) AS MaxCLOID FROM CLO;";
$result = mysql_query($query, $con);
if (mysql_num_rows($result) == 0)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

$row = mysql_fetch_array($result);
$maxCLOID = $row['MaxCLOID'];
$newCLOID = $maxCLOID + 1;
$newIDs = array();

$query =	"SELECT	ID,
					Description,
					CLONumber,
					GROUP_CONCAT(DISTINCT CLOOutcomes.ABETOutcome ORDER BY CLOOutcomes.ABETOutcome ASC SEPARATOR '') AS Outcomes
			FROM CLO, CLOOutcomes
			WHERE	CourseID='$courseID' AND
					CLOOutcomes.CLOID=CLO.ID AND
					CLONumber>'$min' AND
					CLO.ID IN (	SELECT CLOID
								FROM MasterCLO
								WHERE CourseID='$courseID')
			GROUP BY CLO.ID
			ORDER BY CLONumber ASC;";

$result = mysql_query($query, $con);

if (mysql_num_rows($result) > 0)
{
	$oldIDs = array();
	
	// Insert CLOs
	$query = "INSERT INTO CLO (ID, CourseID, CLONumber, Description) VALUES";
	$outcomes = array();
	$cloNumber = $min;
	$first = true;
	while ($row = mysql_fetch_array($result))
	{
		$description = $row['Description'];
		
		if (!$first)
		{
			$query .= ' ,';
		}
		
		$query .= " ('$newCLOID', '$courseID', '$cloNumber', '$description')";
		$oldIDs[] = $row['ID'];
		$newIDs[] = $newCLOID;
		$outcomes[$newCLOID] = $row['Outcomes'];
		$newCLOID++;
		$cloNumber++;
		$first = false;
	}
	$query .= ";";

	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}
	
	// Remove old MasterCLOs
	$query = "DELETE FROM MasterCLO WHERE CourseID='$courseID' AND (";
	$first = true;
	foreach ($oldIDs as $oldID)
	{
		if (!$first)
		{
			$query .= " OR ";
		}
		$query .= "CLOID='$oldID'";
		$first = false;
	}
	$query .= ');';

	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}
	
	// Insert into MasterCLO
	$query = "INSERT INTO MasterCLO (CLOID, CourseID) VALUES";
	$first = true;
	foreach ($newIDs as $newID)
	{
		if (!$first)
		{
			$query .= ' ,';
		}
		
		$query .= " ('$newID', '$courseID')";
		$first = false;
	}
	$query .= ";";

	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}

	// Insert into CLOOutcomes
	$query = "INSERT INTO CLOOutcomes (CLOID, ABETOutcome) VALUES";
	$first = true;
	foreach ($newIDs as $newID)
	{
		foreach (str_split($outcomes[$newID]) as $char)
		{
			if (ctype_alpha($char))
			{
				if (!$first)
				{
					$query .= ', ';
				}
				
				$query .= "('$newID', '$char')";
				$first = false;
			}
		}
	}
	$query .= ";";

	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}
}

// Commit
mysql_query('COMMIT;', $con);

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>