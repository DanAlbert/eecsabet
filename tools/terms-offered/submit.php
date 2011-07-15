<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];

$summer = 0;
$fall = 0;
$winter = 0;
$spring = 0;

if (isset($_REQUEST['summer']) && ($_REQUEST['summer'] == 'on'))
{
	$summer = 1;
}

if (isset($_REQUEST['fall']) && ($_REQUEST['fall'] == 'on'))
{
	$fall = 1;
}

if (isset($_REQUEST['winter']) && ($_REQUEST['winter'] == 'on'))
{
	$winter = 1;
}

if (isset($_REQUEST['spring']) && ($_REQUEST['spring'] == 'on'))
{
	$spring = 1;
}

try
{
	$sth = $dbh->prepare(
		"CALL UpdateTermsOffered(:id, :summer, :fall, :winter, :spring)");
	
	$sth->bindParam(':id', $courseID);
	$sth->bindParam(':summer', $summer);
	$sth->bindParam(':fall', $fall);
	$sth->bindParam(':winter', $winter);
	$sth->bindParam(':spring', $spring);
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

header('Location: ../index.php?courseID=' . $courseID);

?>
