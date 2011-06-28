<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$prerequisiteID = mysql_real_escape_string($_POST['prerequisiteID']);
$alts = $_POST['alt'];
$isCorequisite = mysql_real_escape_string($_POST['isCorequisite']);

$isCoreq = 0;
if ($isCorequisite == 'on')
{
	$isCoreq = 1;
}

mysql_query('START TRANSACTION;');

// Insert into Prerequisites
$query = "INSERT INTO Prerequisites (CourseID, PrerequisiteID, IsCorequisite) VALUES ('$courseID', '$prerequisiteID', '$isCoreq');";
if (mysql_query($query, $con) === false)
{
	print 'Could not insert into Prerequisites: ' . mysql_error() . '<br />';
	print $query;
	mysql_query('ROLLBACK;');
	mysql_close($con);
	return;
}

if (sizeof($alts) > 0)
{
	// Insert into PrerequisiteAlternatives
	$first = true;
	$query = "INSERT INTO PrerequisiteAlternatives (CourseID, PrerequisiteID, AlternativeID) VALUES ";
	foreach ($alts as $alt)
	{
		if (!$first)
		{
			$query .= ", ";
		}
		$query .= "('$courseID', '$prerequisiteID', '$alt'), ('$courseID', '$alt', '$prerequisiteID')";
		$first = false;
	}
	$query .= ";";

	if (mysql_query($query, $con) === false)
	{
		print 'Could not insert into PrerequisiteAlternatives: ' . mysql_error() . '<br />';
		print $query;
		mysql_query('ROLLBACK;');
		mysql_close($con);
		return;
	}
}

mysql_query('COMMIT;');

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>