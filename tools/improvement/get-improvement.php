<?php

include_once '../../debug.php';
require_once '../../db.php';

$dept = $_POST['dept'];
$outcome = $_POST['outcome'];

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare("CALL GetOutcomeImprovement(:dept, :outcome)");
	
	$sth->bindParam(':dept', $dept);
	$sth->bindParam(':outcome', $outcome);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();

if (sizeof($rows) > 0)
{
	print $rows[0]->Improvement;
}

?>