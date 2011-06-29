<?php

require_once '../../db.php';

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

if (!$prereqs)
{
	$prereqs = 'None';
}

if (!$coreqs)
{
	$coreqs = 'None';
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

$query = "SELECT LastRevision FROM SyllabusTimestamp;";
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

\noindent \textbf{Course Content:} 

\begin{itemize*}
' . $contentString . '
\end{itemize*}

\noindent\textbf{Measurable Student Leaning Outcomes:}\\\\
At the completion of the course, students will be able to...
\begin{enumerate*}
' . $cloString . '
\end{enumerate*}

\noindent\textbf{Learning Resources:}
\begin{itemize*}
' . $resourceString . '
\end{itemize*}

\noindent\textbf{Student with Disabilites:}\\\\
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

header('Location: ' . $filename);

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
