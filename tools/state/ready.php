<?php

require_once '../../db.php';

$con = dbConnect();
if (!$con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$termID = $_POST['TermID'];

mysql_query('BEGIN TRANSACTION;', $con);

$query = "UPDATE TermState SET State='Finalized' WHERE TermID='$termID'";
if (!mysql_query($query, $con))
{
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	die("Could not update TermState: " . mysql_error());
}

$query = "UPDATE CourseInstance SET State='Ready' WHERE TermID='$termID'";
if (!mysql_query($query, $con))
{
	mysql_query('ROLLBACK;', $con);
	mysql_close($con);
	die("Could not update CourseInstance: " . mysql_error());
}

mysql_query('COMMIT;', $con);

$instructors = array();
$query =	"SELECT DISTINCT	Instructor.Name,
								Instructor.Email
			FROM CourseInstance, Instructor
			WHERE	CourseInstance.TermID=(SELECT MAX(TermState.TermID) FROM TermState) AND
					Instructor.Email=CourseInstance.Instructor;";

$result = mysql_query($query, $con);
while ($row = mysql_fetch_array($result))
{
	$instructors[$row['Email']] = $row['Name'];
}

$pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

$subject = "Your courses are ready for finalization";
$body = "Your courses are ready for finalization of their ABET accredidation information. Please provide this information soon. To do so, visit the following pages:";

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.orst.edu' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

while (($instructor = current($instructors)) !== false)
{
	$to = key($instructors);
	
	$query =	"SELECT DISTINCT	CONCAT(Course.Dept, ' ', Course.CourseNumber) AS Course,
									CourseInstance.ID AS InstanceID
				FROM CourseInstance, TermState, Course, Instructor
				WHERE	CourseInstance.TermID=TermState.TermID AND
						TermState.TermID=(SELECT MAX(TermID) FROM TermState) AND
						Course.ID=CourseInstance.CourseID AND
						Instructor.Email='$to';";
	
	$result = mysql_query($query, $con);
	
	$message = '<html><head><title>Ready for Finalization</title><head><body>' . $instructor . ',<br /><br />' . $body . '<br />';
	
	while ($row = mysql_fetch_array($result))
	{
		$message .= '<a href="' . $pageURL . '?courseInstanceID=' . $row['InstanceID'] . '">' . $row['Course'] . '</a><br />';
	}
	
	$message .= '<br />EECS ABET Mailer';
	
	#print "$to<br />$message<br /><br />";
	mail($to, $subject, $message, $headers);
	
	next($instructors);
}

mysql_close($con);

header('Location: index.php');
?>
