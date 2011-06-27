<?php

require_once '../../db.php';

$con = dbConnect();
if (!$con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$instructors = $_POST['instructor'];

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "You've been neglecting your ABET forms";
$body = "You still have courses which you have not provided ABET accredidation information for. Please provide this information soon. To do so, visit the following pages:";

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

while (($instructor = current($instructors)) !== false)
{
	$to = key($instructors);
	
	$query = "SELECT InstanceID, Course FROM NaggingInformation WHERE NaggingInformation.Email='$to';";
	$result = mysql_query($query, $con);
	
	$message = '<html><head><title>Nagging</title><head><body>' . $instructor . ',<br /><br />' . $body . '<br />';
	
	while ($row = mysql_fetch_array($result))
	{
		$message .= '<a href="' . $pageURL . '?courseInstanceID=' . $row['InstanceID'] . '">' . $row['Course'] . '</a><br />';
	}
	
	$message .= '<br />EECS ABET Mailer';
	
	mail($to, $subject, $message, $headers);
	
	next($instructors);
}

mysql_close($con);

header('Location: index.php');
?>
