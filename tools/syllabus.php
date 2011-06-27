<?php

require_once '../db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);

$query = "SELECT * FROM CourseInformation WHERE CourseID='$courseID';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

$dept = $row['Dept'];
$num = $row['CourseNumber'];
$title = $row['Title'];
$descrip = $row['Description'];
$structure = $row['Structure'];
$credits = $row['CreditHours'];
$terms = array('Summer' => $row['Summer'], 'Fall' => $row['Fall'], 'Winter' => $row['Winter'], 'Spring' => $row['Spring']);

$termsString = '';
$first =  true;
while (($term = current($terms)) !== false)
{
	if ($term)
	{
		if (!$first)
		{
			$termsString .= ', ';
		}
		
		$termsString .= key($terms);
	}
	
	next($terms);
}

$prereqs = $row['Prerequisites'];
$coreqs = $row['Corequisites'];


$query = "SELECT GROUP_CONCAT(CONCAT(C1.Dept, ' ', C1.CourseNumber) SEPARATOR ', ') AS Courses FROM Course AS C1, Course AS C2, Prerequisites WHERE Prerequisites.PrerequisiteID=C2.ID AND Prerequisites.CourseID=C1.ID AND C2.ID='$courseID' GROUP BY C2.ID;";
$result = mysql_query($query, $con);

$reqThis = '';

if (mysql_num_rows($result) == 0)
{
	$reqThis = 'None.';
}
else
{
	$row = mysql_fetch_array($result);
	$reqThis = $row['Courses'];
}

$query = "SELECT DISTINCT FirstName, LastName FROM Instructor, CourseInstance WHERE CourseInstance.Instructor=Instructor.Email AND CourseInstance.CourseID='$courseID' ORDER BY LastName ASC, FirstName ASC;";
$result = mysql_query($query, $con);
$instructors = array();
while ($row = mysql_fetch_array($result))
{
	$instructors[] = $row['FirstName'] . ' ' . $row['LastName'];
}

$instructorString = '';
$first = true;
foreach ($instructors as $instructor)
{
	if (!$first)
	{
		$instructorString .= ', ';
	}
	
	$instructorString .= $instructor;
	$first = false;
}

$clos = array();

$query = "SELECT * FROM CourseCLOInformation WHERE CourseID='$courseID' ORDER BY CLONumber;";
$result = mysql_query($query, $con);
while ($row = mysql_fetch_array($result))
{
	$clos[] = $row['Description'] . ' (ABET Outcomes: ' . $row['Outcomes'] . ')';
}

print "<h1>$dept $num - $title</h1>";
print "<h2>Catalog Description:</h2>";
print "<p>$descrip</p>";
print "<strong>Credit Hours:</strong> $credits<br />";
print "<strong>Terms Offered:</strong> $termsString.<br />";

print "<strong>Prerequisites:</strong> ";
if ($prereqs)
{
	print "$prereqs";
}
else
{
	print "None.";
}
print "<br />";

print "<strong>Corequisites:</strong> ";
if ($coreqs)
{
	print "$coreqs";
}
else
{
	print "None.";
}
print "<br />";

print "<strong>Courses that require this as a prerequisite:</strong> $reqThis<br />";
print "<strong>Structure:</strong> $structure<br />";
print "<strong>Instructors:</strong> $instructorString<br />";
print "<h2>Measurable Student Leaning Outcomes:</h2>";
print "At the completion of the course, students will be able to...";
print "<ol>";
foreach ($clos as $clo)
{
	print "<li>$clo</li>";
}
print "</ol>";

?>
