<?php

include_once '../debug.php';
require_once '../db.php';

function printABETSyllabus($courseID)
{
	$dbh = dbConnect();
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT * " .
			"FROM CourseInformation " .
			"WHERE CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$row = $sth->fetch();

	$dept = $row->Dept;
	$num = $row->CourseNumber;
	$title = $row->Title;
	$descrip = $row->Description;
	$structure = $row->Structure;
	$credits = $row->CreditHours;
	
	$terms = array(
		'Summer' => $row->Summer,
		'Fall' => $row->Fall,
		'Winter' => $row->Winter,
		'Spring' => $row->Spring);

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
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT * " .
			"FROM PrerequisiteAlternativesInformation " .
			"WHERE CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$firstPrereq = true;
	$firstCoreq = true;
	while ($row = $sth->fetch())
	{
		if ($row->IsCorequisite)
		{
			if (!$firstCoreq)
			{
				$coreqs .= ', ';
			}
			$coreqs .= $row->Prerequisite;
			$firstCoreq = false;
		}
		else
		{
			if (!$firstPrereq)
			{
				$prereqs .= ', ';
			}
			$prereqs .= $row->Prerequisite;
			$firstPrereq = false;
		}
	}
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT GROUP_CONCAT(" .
						"CONCAT(C1.Dept, ' ', C1.CourseNumber) " .
						"SEPARATOR ', ') AS Courses " .
			"FROM Course AS C1, Course AS C2, Prerequisites " .
			"WHERE	 Prerequisites.PrerequisiteID=C2.ID AND " .
					"Prerequisites.CourseID=C1.ID AND " .
					"C2.ID=:id " .
			"GROUP BY C2.ID");
		
		$sth->bindparam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$reqThis = '';
	
	$rows = $sth->fetchAll();
	
	if (sizeof($rows) == 0)
	{
		$reqThis = 'None.';
	}
	else
	{
		$reqThis = $sth->fetch()->Courses;
	}
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT DISTINCT	FirstName,
								LastName
			FROM	Instructor,
					CourseInstance AS C1
			WHERE	C1.Instructor=Instructor.Email AND
					C1.CourseID=:id AND
					C1.TermID=(
						SELECT MAX(TermID)
						FROM CourseInstance AS C2
						WHERE C1.CourseID=C2.CourseID)
			ORDER BY	LastName ASC,
						FirstName ASC");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$instructors = array();
	while ($row = $sth->fetch())
	{
		$instructors[] = $row->FirstName . ' ' . $row->LastName;
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
	try
	{
		$sth = $dbh->prepare(
			"SELECT Content " .
			"FROM CourseContent WHERE " .
			"CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		$content[] = $row->Content;
	}

	$clos = array();
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT * " .
			"FROM CourseCLOInformation " .
			"WHERE CourseID=:id " .
			"ORDER BY CLONumber");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		$clos[] =
			$row->Description . ' (ABET Outcomes: ' . $row->Outcomes . ')';
	}

	$resources = array();
	try
	{
		$sth = $dbh->prepare(
			"SELECT Resource AS Res " .
			"FROM LearningResources " .
			"WHERE CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		$resources[] = $row->Res;
	}
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT LastRevision " .
			"FROM SyllabusTimestamp " .
			"WHERE CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$syllabusDate = makeDateString($sth->fetch()->LastRevision);
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT Policy, LastRevision " .
			"FROM DisabilitiesPolicy");
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$row = $sth->fetch();
	$policy = $row->Policy;
	$policyDate = makeDateString($row->LastRevision);
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT URL " .
			"FROM StudentConduct");
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$conduct = $sth->fetch()->URL;

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
	$date = $components[0];
	$dateComponents = explode('-', $timestamp);

	$year = $dateComponents[0] . '';
	$month = $dateComponents[1] + 0;
	$day = $dateComponents[2] + 0;

	$year = substr($year, 2); // Only show last two digits.

	return "$month/$day/$year";
}

?>
