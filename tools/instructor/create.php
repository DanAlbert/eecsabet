<?php

$hostname = 'mysql.gingerhq.net';
$username = 'eecsabet';
$password = 'hP5fRjZbZ6KcL7MU';
$database = 'eecsabet';

$con = mysql_connect($hostname, $username, $password);
if (!$con)
{
	mysql_close($con);
	die('Unable to connect to database: ' . mysql_error());
}

if (!mysql_select_db($database))
{
	mysql_close($con);
	die('Unable to select database: ' . mysql_error());
}

$name = mysql_real_escape_string($_POST['name']);
$email = mysql_real_escape_string($_POST['email']);

$termID = $year . $term;

// Insert Instructor
$query = "INSERT INTO Instructor (Name, Email) VALUES ('$name', '$email');";

if (mysql_query($query, $con) === false)
{
	print mysql_error();
	mysql_close($con);
	return;
}

mysql_close($con);

header('Location: ../../index.php');

?>