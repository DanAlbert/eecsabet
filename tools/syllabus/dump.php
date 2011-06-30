<?php

require_once '../../db.php';
require_once 'latex.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$query = 	"SELECT CourseID " .
			"FROM CourseInstance, CurrentTermStateInformation " .
			"WHERE	CourseInstance.TermID=CurrentTermStateInformation.CurrentTerm;";
$result = mysql_query($query, $con);
mysql_close();

while ($row = mysql_fetch_array($result))
{
	generateABETSyllabus($row['CourseID']);
}

header('Location: ../index.php');

?>
