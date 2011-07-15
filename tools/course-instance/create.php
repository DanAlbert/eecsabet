<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$course = $_POST['course'];
$instructor = $_POST['instructor'];
$term = $_POST['term'];
$year = $_POST['year'];

if (($term == '00') OR ($term == '01'))
{
	$year += 1;
}

$termID = $year . $term;

// Insert CourseInstance
try
{
	$sth = $dbh->prepare(
		"CALL CreateCourseInstance(:course, :instructor, :term, @result)");
	
	$sth->bindParam(':course', $course);
	$sth->bindParam(':instructor', $instructor);
	$sth->bindParam(':term', $termID);
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$courseInstance = $dbh->query("SELECT @result AS result")->fetch()->result;
switch ($courseInstance)
{
case -1:
	print 'An error occured while creating the new course instance.';
	break;

case -2:
	print "A duplicate entry exists in the database.";
	return;

default:
	break;
}

try
{
	$sth = $dbh->prepare(
		"SELECT CONCAT(FirstName, ' ', LastName) AS Name " .
		"FROM Instructor " .
		"WHERE Email=:email");
	
	$sth->bindParam(':email', $instructor);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$instructorName = $sth->fetch()->Name;

try
{
	$sth = $dbh->prepare(
		"SELECT CONCAT(Dept, ' ', CourseNumber) AS Course " .
		"FROM Course WHERE ID=:id");
	
	$sth->bindParam(':id', $course);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$courseName = $sth->fetch()->Course;

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "New course requires ABET information";

$body = "You have a new course which you need to provide ABET " .
	"accredidation information for. Please provide this information soon. To " .
	"do so, visit the following page:";

$headers =
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	'From: eecsabet@eecs.oregonstate.edu' . "\r\n" .
	'Reply-To: eecsabet@eecs.oregonstate.edu' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

$message = '<html><head><title>Nagging</title><head><body>' . $instructorName .
	',<br /><br />' . $body . '<br /><a href="' . $pageURL . 
	'?courseInstanceID=' . $courseInstance . '">' . $courseName . '</a><br />' .
	'<br />EECS ABET Mailer';

$to = $instructor;

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