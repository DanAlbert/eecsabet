<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$numbers = $_POST['number'];

$query = "SELECT * FROM CourseCLOInformation WHERE CourseID='$courseID';";
$result = mysql_query($query, $con);

$oldValues = array();
while ($row = mysql_fetch_array($result))
{
	$values = array();
	$values['CLONumber'] = $row['CLONumber'];
	$values['Title'] = $row['Title'];
	$values['Description'] = $row['Description'];
	$values['Outcomes'] = $row['Outcomes'];
	
	$oldValues[$row['CLOID']] = $values;
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
mysql_query('START TRANSACTION;', $con);

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

// Insert CourseInstance
$query = "INSERT INTO CLO (ID, CourseID, CLONumber, Title, Description) VALUES";
$outcomes = array();
$first = true;
foreach ($changed as $changedID)
{
	$cloNumber = $numbers[$changedID];
	$title = $oldValues[$changedID]['Title'];
	$description = $oldValues[$changedID]['Description'];
	
	if (!$first)
	{
		$query .= ' ,';
	}
	
	$query .= " ('$newCLOID', '$courseID', '$cloNumber', '$title', '$description')";
	$newIDs[] = $newCLOID;
	$outcomes[$newCLOID] = $oldValues[$changedID]['Outcomes'];
	$newCLOID++;
	$first = false;
}
$query .= ";";

//print $query . '<br />';
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
foreach ($changed as $changedID)
{
	if (!$first)
	{
		$query .= " OR ";
	}
	$query .= "CLOID='$changedID'";
	$first = false;
}
$query .= ');';

//print $query . '<br />';
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

//print $query . '<br />';
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

//print $query . '<br />';

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Commit
mysql_query('COMMIT;', $con);

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>
