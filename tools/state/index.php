<!DOCTYPE html>
<html>
<head>
	<title>Oregon State EECS ABET</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>
<a href="../index.php">Return to Adminstration Page</a> |
<a href="readme.html">Help</a>
<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare("SELECT * FROM CurrentTermStateInformation");
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$row = $sth->fetch();

$termID = $row->CurrentTerm;
$state = $row->CurrentState;

try
{
	$sth = $dbh->prepare("SELECT * FROM NaggingInformation GROUP BY Email");
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$instructors = array();
while ($row = $sth->fetch())
{
	$instructors[] = array('Name' => $row->FirstName . ' ' . $row->LastName, 'Email' => $row->Email, 'State' => $row->State);
}

$year = floor($termID / 100);
$term = '';
switch ($termID - ($year * 100))
{
case 0:
	$term = 'Summer';
	$year -= 1;
	break;
case 1:
	$term = 'Fall';
	$year -= 1;
	break;
case 2:
	$term = 'Winter';
	break;
case 3:
	$term = 'Spring';
	break;
}

print '<h1>Manage Course States for ' . $term . ' ' . $year . '</h1>';
print '<h2>Current State: ' . $state . '</h2>';

if (($state != 'Ready') AND ($state != 'Finalized'))
{
	print '<form action="ready.php" method="POST">';
	print '<input type="hidden" name="TermID" value="' . $termID . '" />';
	print '<input type="submit" value="Ready Courses for Finalization" />';
	print '</form>';
}

if (sizeof($instructors) == 0)
{
	print '<h3>All instructors have completed their forms.</h3>';
}
else
{
	print '<h3>Instructors that have not filled out their forms:</h3>';
	print '<ul>';

	foreach ($instructors as $instructor)
	{
		print '<li><a href="mailto:' . $instructor['Email'] . '">' . $instructor['Name'] . '</a></li>';
	}
	
	print '</ul>';
	
	print '<form action="nag.php" method="POST">';

	for ($i = 0; $i < sizeof($instructors); $i++)
	{
		print '<input type="hidden" name="instructor[' . $instructors[$i]['Email'] . ']" value="' . $instructors[$i]['Name'] . '" />';
	}

	print '<input type="submit" value="Nag" />';
	print '</form>';
}

?>
</body>
</html>
