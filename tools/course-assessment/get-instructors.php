<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$id = $_POST['id'];
$term = $_POST['term'];

if ($id == '')
{
	print '<option value="">No course selected</option>';
}
else if ($term == '')
{
	print '<option value="">No term selected</option>';
}

try
{
	$sth = $dbh->prepare(
		"SELECT	 Instructor.Email, " .
				"CONCAT(" .
					"Instructor.FirstName, ' ', " .
					"Instructor.LastName) AS Instructor " .
		"FROM CourseInstance, Instructor " .
		"WHERE	 CourseInstance.CourseID=:id AND " .
				"CourseInstance.TermID=:term AND " .
				"CourseInstance.Instructor=Instructor.Email " .
		"ORDER BY	 Instructor.LastName, " .
					"Instructor.FirstName");
	
	$sth->bindParam(':id', $id);
	$sth->bindParam(':term', $term);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while ($row = $sth->fetch())
{
	print '<option value="' . $row->Email . '">' .
		$row->Instructor . '</option>';
}

?>