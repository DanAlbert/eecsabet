<!DOCTYPE html>
<html>
<head>
	<title>Administrative Tools</title>
	<link rel="stylesheet" type="text/css" />
</head>
<body>

<a href="course/index.php">Create a New Course</a> | <a href="instructor/index.php">Add an Instructor</a>

<?php

require_once '../db.php';
	
$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}
if (isset($_REQUEST['courseID']))
{
	print	' | <a href="clo/index.php?courseID=' . $_REQUEST['courseID'] . '">Modify Course Learning Outcomes</a> | ' .
			'<a href="prerequisite/index.php?courseID=' . $_REQUEST['courseID'] . '">Modify Course Prerequisites</a> | ' .
			'<a href="terms-offered/index.php?courseID=' . $_REQUEST['courseID'] . '">Change Terms this Course is Offered</a>';
}

$query = "SELECT ID, Dept, CourseNumber FROM Course ORDER BY Dept ASC, CourseNumber ASC;";
$result = mysql_query($query, $con);

if (mysql_num_rows($result) == 0)
{
	print '<br />No courses found.';
}
else
{
	print ' | <a href="course-instance/index.php">Create a New Course Instance</a>';
	print '<h1>Courses</h1>';
	$first = true;
	while ($row = mysql_fetch_array($result))
	{
		if (!$first)
		{
			print '<br />';
		}
		if (isset($_REQUEST['courseID']) AND ($row['ID'] == $_REQUEST['courseID']))
		{
			print $row['Dept'] . ' ' . $row['CourseNumber'];
		}
		else
		{
			print '<a href="index.php?courseID=' . $row['ID'] . '">' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</a>';
		}
		$first = false;
	}
}

?>

</body>
</html>
