<?php

$hostname = 'mysql.gingerhq.net';
$username = 'eecsabet';
$password = 'hP5fRjZbZ6KcL7MU';
$database = 'eecsabet';

$con = mysql_connect($hostname, $username, $password);
if (!$con)
{
	mysql_close($con);
	die('Unable to connect to database: ' . mysql_error());
}

if (!mysql_select_db($database))
{
	mysql_close($con);
	die('Unable to select database: ' . mysql_error());
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

// Begin
mysql_query('START TRANSACTION;', $con);

// Delete from MasterCLO
$query = "DELETE FROM MasterCLO WHERE CourseID='$courseID' AND (";
$first = true;
foreach ($toRemove as $id)
{
	if (!$first)
	{
		$query .= " OR ";
	}
	$query .= "CLOID='$id'";
	$first = false;
}
$query .= ');';

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	return;
}

// Commit
mysql_query('COMMIT;', $con);

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>