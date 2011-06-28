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

mysql_query('START TRANSACTION;');

// Delete from Prerequisites
$query = "DELETE FROM Prerequisites WHERE CourseID='$courseID' AND (";
$first = true;
foreach ($toRemove as $id)
{
	if (!$first)
	{
		$query .= " OR ";
	}
	$query .= "PrerequisiteID='$id'";
	$first = false;
}
$query .= ');';

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;');
	mysql_close($con);
	return;
}

// Delete from PrerequisiteAlternatives
$query = "DELETE FROM PrerequisiteAlternatives WHERE CourseID='$courseID' AND (";
$first = true;
foreach ($toRemove as $id)
{
	if (!$first)
	{
		$query .= " OR ";
	}
	$query .= "PrerequisiteID='$id' OR AlternativeID='$id'";
	$first = false;
}
$query .= ');';

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;');
	mysql_close($con);
	return;
}

mysql_query('COMMIT;');

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>