<?php

include_once '../../debug.php';
require_once '../../db.php';

function generateABETSyllabus($courseID)
{
	$dbh = dbConnect();
	
	$courseID = $courseID;
	
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
	$structure = str_replace("%", "\\%", $row->Structure);
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
	
	if (!$prereqs)
	{
		$prereqs = 'None';
	}
	
	if (!$coreqs)
	{
		$coreqs = 'None';
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
		
		$sth->bindParam(':id', $courseID);
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
			"SELECT DISTINCT	 FirstName, " .
								"LastName " .
			"FROM	 Instructor," .
					"CourseInstance AS C1 " .
			"WHERE	 C1.Instructor=Instructor.Email AND " .
					"C1.CourseID=:id AND " .
					"C1.TermID=(" .
						"SELECT MAX(TermID) " .
						"FROM CourseInstance AS C2 " .
						"WHERE C1.CourseID=C2.CourseID) " .
			"ORDER BY	 LastName ASC, " .
						"FirstName ASC");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$instructorString = '';
	$first = true;
	while ($row = $sth->fetch())
	{
		if (!$first)
		{
			$instructorString .= ', ';
		}
		
		$instructorString .= $row->FirstName . ' ' . $row->LastName;
		$first = false;
	}
	
	$content = array();
	try
	{
		$sth = $dbh->prepare(
			"SELECT Content " .
			"FROM CourseContent " .
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
		$clos[] = $row->Description .
			' (ABET Outcomes: ' . $row->Outcomes . ')';
	}
	
	$resources = array();
	try
	{
		$sth = $dbh->prepare(
			"SELECT Resource AS Res " .
			"FROM LearningResources " .
			"WHERE CourseID=:id");
		
		$sth->bindparam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		$resources[] = str_replace("_", "\\_", $row->Res);
	}
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT LastRevision " .
			"FROM SyllabusTimestamp");
		
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

	$contentString = '';
	if (sizeof($content) > 0)
	{
		foreach ($content as $c)
		{
			$contentString .= "\\item $c";
		}
	}
	
	$cloString = '';
	foreach ($clos as $clo)
	{
		$cloString .= "\\item $clo";
	}
	
	$resourceString = '';
	if (sizeof($resources) > 0)
	{
		foreach ($resources as $r)
		{
			$resourceString .= "\\item $r";
		}
	}
	
	###############################################################################
	$latex = '\documentclass[12pt]{article}
	\textwidth=7in
	\textheight=9.5in
	\topmargin=-1in
	\headheight=0in
	\headsep=.5in
	\hoffset  -.85in

	\pagestyle{empty}

	\usepackage{mdwlist}

	\renewcommand{\thefootnote}{\fnsymbol{footnote}}
	\begin{document}

	\begin{center}
	{\bf ' . $dept . ' ' . $num . ' - ' . $title . '}
	\end{center}

	\setlength{\unitlength}{1in}

	\renewcommand{\arraystretch}{2}

	\noindent\textbf{Catalog Description:} ' . $descrip . '
	\vskip.20in
	\noindent\textbf{Credits:} ' . $credits . ' \hspace{0.50in} \textbf{Terms Offered:} ' . $termsString . '
	\vskip.20in
	\noindent\textbf{Prerequisites:} ' . $prereqs . '
	\vskip.20in
	\noindent\textbf{Corequisites:} ' . $coreqs . '
	\vskip.20in
	\noindent\textbf{Courses that require this as a prerequisite:} ' . $reqThis . '
	\vskip.20in
	\noindent\textbf{Structure:} ' . $structure . '
	\vskip.20in
	\noindent\textbf{Instructors:} ' . $instructorString . '

	\vspace*{.20in}

	';
	
	if ($contentString)
	{
		$latex .= '\noindent \textbf{Course Content:} 

		\begin{itemize*}
		' . $contentString . '
		\end{itemize*}

		';
	}
	
	$latex .= '\noindent\textbf{Measurable Student Leaning Outcomes:}\\\\
	At the completion of the course, students will be able to...
	\begin{enumerate*}
	' . $cloString . '
	\end{enumerate*}

	';
	
	if ($resourceString)
	{
		$latex .= '\noindent\textbf{Learning Resources:}
		\begin{itemize*}
		' . $resourceString . '
		\end{itemize*}
		
		';
	}
	
	$latex .= '\noindent\textbf{Student with Disabilites:}\\\\
	' . $policy . '\\\\
	\\\\
	\noindent\textbf{Link to Statement of Expectations for Student Conduct:}\\\\
	\underline{' . $conduct . '}\\\\
	\\\\
	Revised: ' . $syllabusDate . '\\\\
	Revised Students with Disabilities: ' . $policyDate . '

	\end{document}';

	if (!file_exists('../../syllabi/'))
	{
		mkdir('../../syllabi/');
	}

	$filename = '../../syllabi/' . $dept . $num . '.tex';
	$handle = fopen($filename, 'w');
	if (!$handle)
	{
		die('Cannot open ' . $filename . '.');
	}

	if (fwrite($handle, $latex) === false)
	{
		fclose($handle);
		die('Cannot write to ' . $filename . '.');
	}

	fclose($handle);
	
	return $filename;
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
