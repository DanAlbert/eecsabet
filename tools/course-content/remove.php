<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$remove = $_POST['remove'];

$toRemove = array();
while ($id = current($remove))
{
	if ($id == 'on')
	{
		$toRemove[] = key($remove);
	}
	next($remove);
}

if (sizeof($toRemove) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=2');
	return;
}

try
{
	$sth = $dbh->prepare("CALL RemoveCourseContent(:id)");
	$sth->bindParam(':id', $id);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

foreach ($toRemove as $id)
{
	try
	{
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
}

header('Location: index.php?courseID=' . $courseID);

?>