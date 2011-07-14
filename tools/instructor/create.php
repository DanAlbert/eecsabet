<?php

require_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];

$result = -1;

// Insert Instructor
try
{
	$sth = $dbh->prepare("CALL CreateInstructor(" .
		":firstName, " .
		":lastName, " .
		":email, " .
		"@result)");

	$sth->bindParam(':firstName', $firstName);
	$sth->bindParam(':lastName', $lastName);
	$sth->bindParam(':email', $email);
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

switch ($dbh->query('SELECT @result AS result')->fetch()->result)
{
case 0:
	header('Location: ../index.php');
	break;

case 2:
	print "$email already exists in the database.";
	return;
	
default:
	print 'An error occured while creating the new instructor.';
	return;
}

?>