<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$firstName = mysql_real_escape_string($_POST['firstName']);
$lastName = mysql_real_escape_string($_POST['lastName']);
$email = mysql_real_escape_string($_POST['email']);

$termID = $year . $term;

// Insert Instructor
$query = "CALL CreateInstructor('$firstName', '$lastName', '$email');";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case 1:
	mysql_close($con);
	header('Location: ../index.php');
	break;

case -2:
	mysql_close($con);
	print "$email already exists in the database.";
	return;
	
default:
	print 'An error occured while creating the new instructor.';
	mysql_close($con);
	return;
}

?>