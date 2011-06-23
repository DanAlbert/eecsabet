<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$prerequisiteID = mysql_real_escape_string($_POST['prerequisiteID']);
$isCorequisite = mysql_real_escape_string($_POST['isCorequisite']);

$isCoreq = 0;
if ($isCorequisite == 'on')
{
	$isCoreq = 1;
}

// Insert into Prerequisites
$query = "INSERT INTO Prerequisites (CourseID, PrerequisiteID, IsCorequisite) VALUES ('$courseID', '$prerequisiteID', '$isCoreq');";
if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_close($con);
	return;
}

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>