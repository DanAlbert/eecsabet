<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$resource = mysql_real_escape_string($_POST['resource']);

// Insert LearningResources
$query = "CALL CreateLearningResource('$courseID', '$resource');";
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
	print "$resource already exists in the database for this course.";
	return;
	
default:
	print 'An error occured while creating the new learning resource.';
	mysql_close($con);
	return;
}

?>