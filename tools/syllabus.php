<?php

require_once '../db.php';

function printABETSyllabus($courseID)
{
	$con = dbConnect();
	if (!con)
	{
		die('Unable to connect to database: ' . mysql_error());
	}

	$courseID = mysql_real_escape_string($courseID);

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
			$first = false;
		}
		
		next($terms);
	}

	$prereqs = '';
	$coreqs = '';

	$query = "SELECT * FROM PrerequisiteAlternativesInformation WHERE CourseID='$courseID';";
	$result = mysql_query($query, $con);
	$firstPrereq = true;
	$firstCoreq = true;
	while ($row = mysql_fetch_array($result))
	{
		if ($row['IsCorequisite'])
		{
			if (!$firstCoreq)
			{
				$coreqs .= ', ';
			}
			$coreqs .= $row['Prerequisite'];
			$firstCoreq = false;
		}
		else
		{
			if (!$firstPrereq)
			{
				$prereqs .= ', ';
			}
			$prereqs .= $row['Prerequisite'];
			$firstPrereq = false;
		}
	}

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
	
	$query = "	SELECT DISTINCT	FirstName,
								LastName
				FROM	Instructor,
						CourseInstance
				WHERE	CourseInstance.Instructor=Instructor.Email AND
						CourseInstance.CourseID='2' AND
						CourseInstance.TermID=(SELECT MAX(TermID) FROM CourseInstance WHERE CourseInstance.CourseID='2')
				ORDER BY	LastName ASC,
							FirstName ASC;";
	
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

	$content = array();
	$query = "SELECT Content FROM CourseContent WHERE CourseID='$courseID';";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		$content[] = $row['Content'];
	}

	$clos = array();

	$query = "SELECT * FROM CourseCLOInformation WHERE CourseID='$courseID' ORDER BY CLONumber;";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		$clos[] = $row['Description'] . ' (ABET Outcomes: ' . $row['Outcomes'] . ')';
	}

	$resources = array();
	$query = "SELECT Resource FROM LearningResources WHERE CourseID='$courseID';";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		$resources[] = $row['Resource'];
	}

	$query = "SELECT LastRevision FROM SyllabusTimestamp WHERE CourseID='$courseID';";
	$result = mysql_query($query, $con);
	$row = mysql_fetch_array($result);
	$syllabusDate = makeDateString($row['LastRevision']);

	$query = "SELECT Policy, LastRevision FROM DisabilitiesPolicy;";
	$result = mysql_query($query, $con);
	$row = mysql_fetch_array($result);
	$policy = $row['Policy'];
	$policyDate = makeDateString($row['LastRevision']);

	$query = "SELECT URL FROM StudentConduct;";
	$result = mysql_query($query, $con);
	$row = mysql_fetch_array($result);
	$conduct = $row['URL'];

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

	if (sizeof($content) > 0)
	{
		print "<h2>Course Content</h2>";
		print "<ul>";
		foreach ($content as $c)
		{
			print "<li>$c</li>";
		}
		print "</ul>";
	}

	print "<h2>Measurable Student Leaning Outcomes:</h2>";
	print "At the completion of the course, students will be able to...";
	print "<ol>";
	foreach ($clos as $clo)
	{
		print "<li>$clo</li>";
	}
	print "</ol>";

	if (sizeof($resources) > 0)
	{
		print "<h2>Learning Resources</h2>";
		print "<ul>";
		foreach ($resources as $r)
		{
			print "<li>$r</li>";
		}
		print "</ul>";
	}

	print "<h2>Student with Disabilites:</h2>";
	print "<p>$policy</p>";

	print "<h2>Link to Statement of Expectations for Student Conduct:</h2>";
	print "<a href=\"$conduct\">$conduct</a><br />";
	print "<br />";
	print "Revised: $syllabusDate<br />";
	print "Revised Students with Disabilities: $policyDate";
}

function makeDateString($timestamp)
{
	$components = explode(' ', $timestamp);
	$date = $policyComponents[0];
	$dateComponents = explode('-', $timestamp);

	$year = $dateComponents[0] . '';
	$month = $dateComponents[1] + 0;
	$day = $dateComponents[2] + 0;

	$year = substr($year, 2); // Only show last two digits.

	return "$month/$day/$year";
}

?>
