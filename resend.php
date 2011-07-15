<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
</head>
<body>
<?php

include_once 'debug.php';
require_once 'db.php';

# Email info
const $pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';
const $subject = "Your courses that require ABET information";

const $body =
	"These are the courses we have you listed as teaching this term. Please " .
	"provide any missing information soon. To do so, visit the following " .
	"pages:";

const $headers  =
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	'From: eecsabet@eecs.orst.edu' . "\r\n" .
	'Reply-To: eecsabet@eecs.orst.edu' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

$dbh = dbConnect();

$email = $_POST['email'];

try
{
	$sth = $dbh->prepare(
		"SELECT CONCAT(FirstName, ' ', LastName) AS Name " .
		"FROM Instructor " .
		"WHERE Email=:email");

	$sth->bindParam(':email', $email);

	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$instructorName = $sth->fetch()->Name;

$to = $email;

$message =
	'<html><head><title>Nagging</title><head><body>' . 
	$instructorName . ',<br /><br />' . $body . '<br />';

try
{
	$sth = $dbh->prepare(
		"SELECT	CourseInstance.ID AS InstanceID,
				CONCAT(Dept, ' ', CourseNumber) AS Course
		FROM Course, CourseInstance, CurrentTerm
		WHERE	Course.ID=CourseInstance.CourseID AND
				CourseInstance.TermID=(SELECT TermID FROM CurrentTerm LIMIT 1) AND
				CourseInstance.Instructor=:email;";

	$sth->bindParam(':email', $email);

	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

while ($row = $sth->fetch())
{
	$courseInstance = $row->InstanceID;
	$courseName = $row->Course;
	$message .=
		'<a href="' . $pageURL . '?courseInstanceID=' . $courseInstance . '">' .
		$courseName . '</a><br />';
}

$message .= '<br />EECS ABET Mailer';

if (mail($to, $subject, $message, $headers))
{
	print '<p>An email has been sent to you containing links to your courses.' .
	'</p>';
}
else
{
	print '<p>An error occured while sending mail.</p>';
}

?>
<a href="index.php">Go Back</a>
</body>
</html>
