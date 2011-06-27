<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$course = mysql_real_escape_string($_POST['course']);
$instructor = mysql_real_escape_string($_POST['instructor']);
$term = mysql_real_escape_string($_POST['term']);
$year = mysql_real_escape_string($_POST['year']);

if (($term == '00') OR ($term == '01'))
{
	$year += 1;
}

$termID = $year . $term;

// Begin
mysql_query('START TRANSACTION;', $con);

// Insert CourseInstance
$query = "INSERT INTO CourseInstance (CourseID, Instructor, TermID) VALUES ('$course', '$instructor', '$termID');";

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Retrieve new instance ID
$query = "SELECT ID FROM CourseInstance WHERE CourseID='$course' AND Instructor='$instructor' AND TermID='$termID';";

$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$courseInstance = $row['ID'];

if ($courseInstance == '')
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Insert CourseInstanceCLOs
$query = "SELECT CLOID FROM MasterCLO WHERE CourseID='$course';";
$result = mysql_query($query, $con);
while ($row = mysql_fetch_array($result))
{
	$query = "INSERT INTO CourseInstanceCLO (CLOID, CourseInstanceID) VALUES ('" . $row['CLOID'] . "', '$courseInstance');";
	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}
}

$query = "SELECT * FROM TermStateInformation WHERE Term='$termID';";
$result = mysql_query($query, $con);
if (mysql_num_rows($result) == 0)
{
	$query = "INSERT INTO TermState (TermID, State) VALUES ('$termID', 'Approved');";
	if (mysql_query($query, $con) === false)
	{
		print mysql_error();
		mysql_query('ROLLBACK;', $con);
		mysql_close($con);
		return;
	}
}

// TODO: Mail, and change header()

// Commit
mysql_query('COMMIT;', $con);

mysql_close($con);

header('Location: ../../index.php?courseInstanceID=' . $courseInstance);

?>