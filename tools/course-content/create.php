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
$query = "INSERT INTO CourseContent (CourseID, Content) VALUES ('$courseID', '$content');";
if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_close($con);
	return;
}

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>