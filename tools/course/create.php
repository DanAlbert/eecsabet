<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$dept = mysql_real_escape_string($_POST['dept']);
$courseNumber = mysql_real_escape_string($_POST['courseNumber']);
$creditHours = mysql_real_escape_string($_POST['creditHours']);
$description = mysql_real_escape_string($_POST['description']);

// Begin
mysql_query('START TRANSACTION;', $con);

// Insert CourseInstance
$query = "INSERT INTO Course (Dept, CourseNumber, CreditHours, Description) VALUES ('$dept', '$courseNumber', '$creditHours', '$description');";

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Retrieve new course ID
$query = "SELECT ID FROM Course WHERE Dept='$dept' AND CourseNumber='$courseNumber';";

$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$courseID = $row['ID'];

if ($courseID == '')
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

$query = "INSERT INTO TermsOffered (CourseID, Summer, Fall, Winter, Spring) VALUES ('$courseID', '0', '0', '0', '0');";
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

header('Location: ../clo/index.php?courseID=' . $courseID);

?>