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

// Insert Course
$query = "CALL CreateCourse('$dept', '$courseNumber', '$courseTitle', '$creditHours', '$description', '$structure');";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case -1:
	print 'An error occured while creating the new course.';
	mysql_close($con);
	return;
	
case -2:
	mysql_close($con);
	print "$dept $courseNumber already exists in the database.";
	return;
}

$courseID = $row[0];

mysql_close($con);

header('Location: ../clo/index.php?courseID=' . $courseID);

?>