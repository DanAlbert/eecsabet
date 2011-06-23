<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$title = mysql_real_escape_string($_POST['title']);
$description = mysql_real_escape_string($_POST['description']);
$outcomeString = mysql_real_escape_string($_POST['outcomes']);

$outcomes = array();
foreach (str_split($outcomeString) as $char)
{
	if (ctype_alpha($char))
	{
		$outcomes[] = $char;
	}
}

if (sizeof($outcomes) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=1');
}

// Begin
mysql_query('START TRANSACTION;', $con);

// Find highest CLO Number
$query = "SELECT MAX(CLO.CLONumber) AS MaxCLONumber FROM MasterCLO, CLO WHERE MasterCLO.CourseID='$courseID' AND MasterCLO.CLOID=CLO.ID;";
$result = mysql_query($query, $con);
if (mysql_num_rows($result) == 0)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

$row = mysql_fetch_array($result);
$maxCLONumber = $row['MaxCLONumber'];
$newCLONumber = $maxCLONumber + 1;

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

// Insert CourseInstance
$query = "INSERT INTO CLO (ID, CourseID, CLONumber, Title, Description) VALUES ('$newCLOID', '$courseID', '$newCLONumber', '$title', '$description');";
if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Insert into MasterCLO
$query = "INSERT INTO MasterCLO (CLOID, CourseID) VALUES ('$newCLOID', '$courseID');";
if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Insert into CLOOutcomes
$query = "INSERT INTO CLOOutcomes (CLOID, ABETOutcome) VALUES ";
$first = true;
foreach ($outcomes as $outcome)
{
	if (!$first)
	{
		$query .= ', ';
	}
	
	$query .= "('$newCLOID', '$outcome')";
	$first = false;
}

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