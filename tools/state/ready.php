<?php

require_once '../../db.php';

$con = dbConnect();
if (!$con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$termID = $_POST['TermID'];

$query = "CALL AllowFinalize('$termID')";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case -1:
	print 'An error occured while removing the course content.';
	mysql_close($con);
	return;
	
case 1:
	break;
	
default:
	print $query . ': ERROR (' . mysql_errno() . ')<br />';
	break;
}

$instructors = array();

// Reconnect if connection is lost
if (mysql_ping($con) === false)
{
	$con = dbConnect();
	if (!con)
	{
		die('Unable to connect to database: ' . mysql_error());
	}
}
$query = "CALL GetInstructorsByTerm('$termID')";
$result = mysql_query($query, $con);
while ($row = mysql_fetch_array($result))
{
	$instructors[$row['Email']] = $row['Name'];
}

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "Your courses are ready to be finalized";
$body = "Your courses are ready to be finalized of their ABET accredidation information. Please provide this information soon. To do so, visit the following pages:";

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

while (($instructor = current($instructors)) !== false)
{
	$to = key($instructors);
	
	// Reconnect if connection is lost
	if (mysql_ping($con) === false)
	{
		$con = dbConnect();
		if (!con)
		{
			die('Unable to connect to database: ' . mysql_error());
		}
	}
	
	$query = "CALL GetUnfinishedCourses('$to', '$termID')";
	$result = mysql_query($query, $con);
	
	$message = '<html><head><title>Ready to be Finalized</title><head><body>' . $instructor . ',<br /><br />' . $body . '<br />';
	
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
