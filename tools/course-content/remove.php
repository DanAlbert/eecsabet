<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
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

foreach ($toRemove as $id)
{
	$query = "CALL RemoveCourseContent('$id')";
	$result = false;
	
	// Reconnect if connection is lost
	if (mysql_ping($con) === false)
	{
		$con = dbConnect();
		if (!con)
		{
			die('Unable to connect to database: ' . mysql_error());
		}
	}
	
	$result = mysql_query($query, $con);
	$row = mysql_fetch_array($result);
	switch ($row[0])
	{
	case -1:
		print 'An error occured while removing the course content.';
		mysql_close($con);
		return;
		
	case 1:
		break;
		
	default:
		print $query . ': ERROR (' . mysql_errno() . ')<br />';
		break;
	}
}

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>