<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$id = $_POST['id'];

if ($id == '')
{
	print '<option value="">No course selected</option>';
}

try
{
	$sth = $dbh->prepare(
		"SELECT DISTINCT TermID " .
		"FROM CourseInstance, Course " .
		"WHERE Course.ID=:id AND Course.ID=CourseInstance.CourseID " .
		"ORDER BY TermID ASC");
	
	$sth->bindParam(':id', $id);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
foreach ($rows as $row)
{
	$termID = $row->TermID;
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
	
	print '<option value="' . $termID . '">' .
		$term . ' ' . $year . '</option>';
}

?>