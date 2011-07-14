<?php

require_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];
$description = $_POST['description'];
$outcomeString = $_POST['outcomes'];

$outcomes = '';
foreach (str_split($outcomeString) as $char)
{
	if (ctype_alpha($char))
	{
		$outcomes .= $char;
	}
}

if (sizeof($outcomes) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=1');
}

try
{
	$sth = $dbh->prepare("CALL CreateCLO(:id, :desc, :outcomes, @result)");
	$sth->bindParam(':id', $courseID);
	$sth->bindParam(':desc', $description);
	$sth->bindParam(':outcomes', $outcomes);

	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

switch ($dbh->query('SELECT @result AS result')->fetch()->result)
{
case 0:
	print 'An error occured while creating the new CLO.';
	return;
}

header('Location: index.php?courseID=' . $courseID);

?>