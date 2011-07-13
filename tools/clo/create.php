<?php

require_once '../../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);
$description = mysql_real_escape_string($_POST['description']);
$outcomeString = mysql_real_escape_string($_POST['outcomes']);

$outcomes = '';
foreach (str_split($outcomeString) as $char)
{
	if (ctype_alpha($char))
	{
		$outcomes .= $char;
	}
}

if (sizeof($outcomes) == 0)
{
	header('Location: index.php?courseID=' . $courseID . '&error=1');
}

$query = "CALL CreateCLO('$courseID', '$description', '$outcomes')";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);
switch ($row[0])
{
case 0:
	print 'An error occured while creating the new CLO.';
	mysql_close($con);
	return;
}

mysql_close($con);

header('Location: index.php?courseID=' . $courseID);

?>