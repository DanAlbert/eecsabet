<?php

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);

$prep = mysql_real_escape_string($_POST['prep']);
$prepActions = mysql_real_escape_string($_POST['prepActions']);
$changes = mysql_real_escape_string($_POST['changes']);
$clo = mysql_real_escape_string($_POST['clo']);
$recs = mysql_real_escape_string($_POST['recs']);

$query =	"UPDATE CourseInstance SET " .
			"CommentPrep='$prep', " .
			"CommentPrepActions='$prepActions', " .
			"CommentChanges='$changes', " .
			"CommentCLO='$clo', " .
			"CommentRecs='$recs' " .
			"WHERE ID='$courseInstanceID';";
mysql_query($query, $con);
if (mysql_errno() != 0)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID . '&error=5');
	return;
}

mysql_close($con);

header('Location: index.php?courseInstanceID=' . $courseInstanceID);

?>
