<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare(
		"SELECT	 ID, " .
				"CONCAT(Dept, ' ', CourseNumber) AS Course " .
		"FROM Course " .
		"WHERE Course.ID IN (	SELECT CourseID
								FROM CourseInstance
								WHERE State='Finalized') " .
		"ORDER BY	 Dept, " .
					"CourseNumber");
	
	$sth->bindParam(':term', $term);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
if (sizeof($rows) == 0)
{
	print '<option value="">No finished courses found</option>';
}

foreach ($rows as $row)
{
	print '<option value="' . $row->ID . '">' .
		$row->Course . '</option>';
}

?>