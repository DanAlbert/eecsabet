<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$termID = $_POST['TermID'];

try
{
	$sth = $dbh->prepare("CALL AllowFinalize(:id)");
	$sth->bindParam(':id', $termID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$instructors = array();

try
{
	$sth = $dbh->prepare("CALL GetInstructorsByTerm(:id)");
	$sth->bindParam(':id', $termID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while ($row = $sth->fetch())
{
	$instructors[$row->Email] = $row->Name;
}

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "Your courses are ready to be finalized";
$body = "Your courses are ready to be finalized of their ABET " .
	"accredidation information. Please provide this information soon. To do " .
	"so, visit the following pages:";

$headers =
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	'From: eecsabet@eecs.orst.edu' . "\r\n" .
	'Reply-To: eecsabet@eecs.orst.edu' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

try
{
	$sth = $dbh->prepare("CALL GetUnfinishedCourses(:email, :term)");
	$sth->bindParam(':email', $to);
	$sth->bindParam(':term', $termID);
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while (($instructor = current($instructors)) !== false)
{
	$to = key($instructors);
	
	try
	{
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$message = '<html><head><title>Ready to be Finalized</title><head><body>' .
		$instructor . ',<br /><br />' . $body . '<br />';
	
	while ($row = $sth->fetch())
	{
		$message .= '<a href="' . $pageURL . '?courseInstanceID=' .
			$row->InstanceID . '">' . $row->Course . '</a><br />';
	}
	
	$message .= '<br />EECS ABET Mailer';
	
	mail($to, $subject, $message, $headers);
	
	next($instructors);
}

header('Location: index.php');

?>
