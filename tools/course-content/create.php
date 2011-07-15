<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$content = $_POST['content'];

// Insert CourseContent
try
{
	$sth = $dbh->prepare("CALL CreateCourseContent(:id, :content, @result)");
	$sth->bindParam(':id', $courseID);
	$sth->bindParam(':content', $content);

	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getmessage());
}

switch ($dbh->query("SELECT @result AS result")->fetch()->result)
{
case 0:
	header('Location: index.php?courseID=' . $courseID);
	break;

case -2:
	print "$content already exists in the database for this course.";
	break;
	
default:
	print 'An error occured while creating the new course content.';
	break;
}

?>
