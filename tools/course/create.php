<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$dept = mysql_real_escape_string($_POST['dept']);
$courseNumberString = mysql_real_escape_string($_POST['courseNumber']);
$courseTitle = mysql_real_escape_string($_POST['courseTitle']);
$creditHours = mysql_real_escape_string($_POST['creditHours']);
$description = mysql_real_escape_string($_POST['description']);
$structure = mysql_real_escape_string($_POST['structure']);

$courseNumber = '';
foreach (str_split($courseNumberString) as $char)
{
	if (ctype_digit($char))
	{
		$courseNumber .= $char;
	}
}

// Begin
mysql_query('START TRANSACTION;', $con);

// Insert Course
$query = "	INSERT INTO Course (Dept, CourseNumber, Title, CreditHours, Description, Structure)
			VALUES ('$dept', '$courseNumber', '$courseTitle', '$creditHours', '$description', '$structure');";

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

// TODO: trigger
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