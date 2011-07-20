<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$dept = $_POST['dept'];
$outcome = $_POST['outcome'];
$text = $_POST['improvement'];

try
{
	$sth = $dbh->prepare(
		"UPDATE Outcomes SET Improvement=:text " .
		"WHERE Dept=:dept AND Outcome=UPPER(:outcome)");
	
	$sth->bindParam(':text', $text);
	$sth->bindParam(':dept', $dept);
	$sth->bindParam(':outcome', $outcome);
	
	$sth->execute();
}
catch (PDOObject $e)
{
	die('PDOException: ' . $e->getMessage());
}

header('Location: index.php');
return;

?>
