<!DOCTYPE html>
<html>
<head>
	<title>Administrative Tools</title>
	<link rel="stylesheet" type="text/css" href="../style.css" />
</head>
<body>

<a href="readme.html">Help</a>

<!--<a href="state/index.php">Manage Course States</a>  |
<a href="syllabus/dump.php">Generate All LaTeX Syllabi</a>-->

<h5>Curriculum and Program Outcome Information</h5>
<a href="program-outcome/index.php">Program Outcome Information</a> |
<a href="improvement/index.php">Curriculum Improvement Messages</a> |
<a href="course-assessment/index.php">Course Assessments</a>

<h5>Course Offerings</h5>
<a href="instructor/index.php">Add an Instructor</a> |
<a href="course-instance/index.php">Create a Course Offering</a>

<h5>Course Creation</h5>
<a href="course/index.php">Create a Course</a>

<?php

include_once '../debug.php';
require_once '../db.php';
require_once 'syllabus.php';
	
$dbh = dbConnect();

if (isset($_REQUEST['courseID']))
{
	print
		' | <a href="clo/index.php?courseID=' . $_REQUEST['courseID'] .
		'">Course Learning Outcomes</a> | ' .
		'<a href="course-content/index.php?courseID=' .	$_REQUEST['courseID'] .
		'">Course Content</a> | ' .
		'<a href="learning-resources/index.php?courseID=' .
		$_REQUEST['courseID'] . '">Learning Resources</a> | ' .
		'<a href="prerequisite/index.php?courseID=' . $_REQUEST['courseID'] .
		'">Prerequisites</a> | ' .
		'<a href="terms-offered/index.php?courseID=' . $_REQUEST['courseID'] .
		'">Terms Offered</a>';
}
else
{
	print '<h5>More tools will appear once you have selected a course</h5>';
}

try
{
	$sth = $dbh->prepare(
		"SELECT ID, Dept, CourseNumber " .
		"FROM Course " .
		"ORDER BY Dept ASC, CourseNumber ASC");
	
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
if (sizeof($rows) == 0)
{
	print '<br />No courses found.';
}
else
{
	print '<h1>Courses</h1>';
	$first = true;
	foreach ($rows as $row)
	{
		if (!$first)
		{
			print '<br />';
		}
		if (isset($_REQUEST['courseID']) AND
			($row->ID == $_REQUEST['courseID']))
		{
			print $row->Dept . ' ' . $row->CourseNumber;
		}
		else
		{
			print '<a href="index.php?courseID=' . $row->ID . '">' .
				$row->Dept . ' ' . $row->CourseNumber . '</a>';
		}
		$first = false;
	}
}

if (isset($_REQUEST['courseID']))
{
	print '<br /><br /><a href="syllabus/index.php?courseID=' .
		$_REQUEST['courseID'] .
		'">Download this course\'s ABET syllabus in LaTeX format</a>';
}

if (isset($_REQUEST['courseID']))
{
	printABETSyllabus($_REQUEST['courseID']);
}

?>

</body>
</html>
