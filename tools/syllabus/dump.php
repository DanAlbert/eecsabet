<?php

include_once '../../debug.php';
require_once '../../db.php';
require_once 'latex.php';

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare(
		"SELECT CourseID " .
		"FROM CourseInstance, CurrentTerm " .
		"WHERE CourseInstance.TermID=CurrentTerm.TermID");
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while ($row = $sth->fetch())
{
	generateABETSyllabus($row->CourseID);
}

header('Location: ../index.php');

?>
