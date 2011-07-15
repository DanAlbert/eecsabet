<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$instructors = $_POST['instructor'];

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "You've been neglecting your ABET forms";
$body = "You still have courses which you have not provided ABET " .
	"accredidation information for. Please provide this information soon. To " .
	"do so, visit the following pages:";

$headers =
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	'From: eecsabet@eecs.orst.edu' . "\r\n" .
	'Reply-To: eecsabet@eecs.orst.edu' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

try
{
	$sth = $dbh->prepare(
		"SELECT InstanceID, Course FROM " .
		"NaggingInformation " .
		"WHERE NaggingInformation.Email=:email");
	
	$sth->bindParam(':email', $to);
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
	
	$message = '<html><head><title>Nagging</title><head><body>' . $instructor .
		',<br /><br />' . $body . '<br />';
	
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
