<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$dept = $_POST['dept'];
$courseNumberString = $_POST['courseNumber'];
$courseTitle = $_POST['courseTitle'];
$creditHours = $_POST['creditHours'];
$description = $_POST['description'];
$structure = $_POST['structure'];

$courseNumber = '';
foreach (str_split($courseNumberString) as $char)
{
	if (ctype_digit($char))
	{
		$courseNumber .= $char;
	}
}

// Insert Course
try
{
	$sth = $dbh->prepare("CALL CreateCourse(" .
		":dept, " .
		":courseNumber, " .
		":courseTitle, " .
		":creditHours, " .
		":description, " .
		":structure, " .
		"@result)");

	$sth->bindParam(':dept', $dept);
	$sth->bindParam(':courseNumber', $courseNumber);
	$sth->bindParam(':courseTitle', $courseTitle);
	$sth->bindParam(':creditHours', $creditHours);
	$sth->bindParam(':description', $description);
	$sth->bindParam(':structure', $structure);
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$courseID = $dbh->query('SELECT @result AS result')->fetch()->result;
switch ($courseID)
{
case -1:
	print 'An error occured while creating the new course.';
	return;
	
case -2:
	print "$dept $courseNumber already exists in the database.";
	return;
}

header('Location: ../clo/index.php?courseID=' . $courseID);

?>