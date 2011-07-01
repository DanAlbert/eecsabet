<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$course = mysql_real_escape_string($_POST['course']);
$instructor = mysql_real_escape_string($_POST['instructor']);
$term = mysql_real_escape_string($_POST['term']);
$year = mysql_real_escape_string($_POST['year']);

if (($term == '00') OR ($term == '01'))
{
	$year += 1;
}

$termID = $year . $term;

// Insert CourseInstance
$query = "CALL CreateCourseInstance('$course', '$instructor', '$termID');";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case -1:
	print 'An error occured while creating the new course instance.';
	mysql_close($con);
	return;
}

$query = "SELECT CONCAT(FirstName, ' ', LastName) AS Name FROM Instructor WHERE Email='$instructor';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$instructorName = $row['Name'];

$query = "SELECT CONCAT(Dept, ' ', CourseNumber) AS Course FROM Course WHERE ID='$course';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
$courseName = $row['Course'];

mysql_close($con);

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$to = $instructor;
$subject = "New course requires ABET information";
$body = "You have a new course which you need to provide ABET accredidation information for. Please provide this information soon. To do so, visit the following page:";

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: eecsabet@eecs.oregonstate.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.oregonstate.edu' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

$message = '<html><head><title>Nagging</title><head><body>' . $instructorName . ',<br /><br />' . $body . '<br />';
$message .= '<a href="' . $pageURL . '?courseInstanceID=' . $courseInstance . '">' . $courseName . '</a><br />';
$message .= '<br />EECS ABET Mailer';

if (mail($to, $subject, $message, $headers) === true)
{
	header('Location: ../index.php');
}
else
{
	print "Could not send mail.<br />";
	print "To: $to<br />";
	print "Subject: $subject<br />";
	print "Headers: $headers<br />";
	print "Body: $message<br />";;
}

?>