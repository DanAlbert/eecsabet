<?php

include_once '../../debug.php';
require_once '../../db.php';

$dept = $_POST['dept'];

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare(
		"SELECT DISTINCT UPPER(Outcome) AS Outcome " .
		"FROM Outcomes " .
		"WHERE Dept=:dept " .
		"ORDER BY Outcome ASC");
	
	$sth->bindParam(':dept', $dept);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while ($row = $sth->fetch())
{
	print '<option value="' . $row->Outcome . '">' .
		$row->Outcome . '</option>';
}

?>