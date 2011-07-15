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
	$sth = $dbh->prepare("CALL RemoveLearningResource(:id)");
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getmessage());
}

foreach ($toRemove as $id)
{
	try
	{
		$sth->bindParam(':id', $id);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getmessage());
	}
}

header('Location: index.php?courseID=' . $courseID);

?>