<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
</head>
<body>
<?php

require_once 'db.php';

$con = dbConnect();
if (!$con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$email = mysql_real_escape_string($_POST['email']);

$query = "SELECT Name FROM Instructor WHERE Email='$email';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$instructorName = $row['Name'];

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$to = $email;
$subject = "Your courses that require ABET information";
$body = "These are the courses we have you listed as teaching this term. Please provide any missing information soon. To do so, visit the following pages:";

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

$message = '<html><head><title>Nagging</title><head><body>' . $instructorName . ',<br /><br />' . $body . '<br />';

$query =	"SELECT	CourseInstance.ID AS InstanceID,
					CONCAT(Dept, ' ', CourseNumber) AS Course
			FROM Course, CourseInstance
			WHERE	Course.ID=CourseInstance.CourseID AND
					CourseInstance.TermID=(SELECT MAX(TermID) FROM TermState) AND
					CourseInstance.Instructor='$email';";

$result = mysql_query($query, $con);
while ($row = mysql_fetch_array($result))
{
	$courseInstance = $row['InstanceID'];
	$courseName = $row['Course'];
	$message .= '<a href="' . $pageURL . '?courseInstanceID=' . $courseInstance . '">' . $courseName . '</a><br />';
}

$message .= '<br />EECS ABET Mailer';

if (mail($to, $subject, $message, $headers))
{
	print '<p>An email has been sent to you containing links to your courses.</p>';
}
else
{
	print '<p>An error occured while sending mail.</p>';
}

?>
<a href="index.php">Go Back</a>
</body>
</html>
