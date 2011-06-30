<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$content = mysql_real_escape_string($_POST['content']);

// Insert CourseContent
$query = "CALL CreateCourseContent('$courseID', '$content');";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case 1:
	mysql_close($con);
	header('Location: index.php?courseID=' . $courseID);
	break;

case -2:
	mysql_close($con);
	print "$content already exists in the database for this course.";
	return;
	
default:
	print 'An error occured while creating the new course content.';
	mysql_close($con);
	return;
}

?>
