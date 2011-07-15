<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$resource = $_POST['resource'];

// Insert LearningResources
try
{
	$sth =
		$dbh->prepare("CALL CreateLearningResource(:id, :resource, @result)");
	
	$sth->bindParam(':id', $courseID);
	$sth->bindParam(':resource', $resource);
	
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
	print "$resource already exists in the database for this course.";
	break;
	
default:
	print 'An error occured while creating the new learning resource.';
	break;
}

?>