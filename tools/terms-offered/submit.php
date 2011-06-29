<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);

$summer = 0;
$fall = 0;
$winter = 0;
$spring = 0;

if ($_REQUEST['summer'] == 'on')
{
	$summer = 1;
}

if ($_REQUEST['fall'] == 'on')
{
	$fall = 1;
}

if ($_REQUEST['winter'] == 'on')
{
	$winter = 1;
}

if ($_REQUEST['spring'] == 'on')
{
	$spring = 1;
}

$query = "UPDATE TermsOffered SET Summer='$summer', Fall='$fall', Winter='$winter', Spring='$spring' WHERE CourseID='$courseID';";
mysql_query($query, $con);

mysql_close($con);

header('Location: ../index.php?courseID=' . $courseID);

?>
