<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<a href="tools/index.php">Administrative Tools</a>
<br />
<?php

if (!isset($_REQUEST['courseInstanceID']))
{
	print '<p>No course instance ID provided. Please use the link provided to you by email to access this page.</p>';
	print '<h2><Resend Email</h2>';
	print '<form action="resend.php" method="POST"><label for="email">Email</label><input id="email" type="text" name="email" /><input type="submit" value="Resend Email" /></form>';
	return;
}

require_once 'db.php';

$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseInstanceID = mysql_real_escape_string($_REQUEST['courseInstanceID']);

$query = "SELECT * FROM CourseInstanceInformation WHERE CourseInstanceID=$courseInstanceID;";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

$termID = $row['TermID'];
$year = floor($termID / 100);
$term = '';
switch ($termID - ($year * 100))
{
case 0:
	$term = 'Summer';
	break;
case 1:
	$term = 'Fall';
	break;
case 2:
	$term = 'Winter';
	break;
case 3:
	$term = 'Spring';
	break;
}

print '<h1>Editing ABET Details for ' . $row['Dept'] . ' ' . $row['CourseNumber'] . ' (' . $row['CreditHours'] . ')</h1>';
print '<h2>' . $term . ' ' . $year . '</h2>';
print '<h3>' . $row['Name'] . '</h3>';
print '<h3>Catalog Description</h3>';
print '<p>' . $row['Description'] . '</p>';

$state = $row['State'];
$prep = $row['CommentPrep'];
$prepActions = $row['CommentPrepActions'];
$changes = $row['CommentChanges'];
$clo = $row['CommentCLO'];
$recs = $row['CommentRecs'];

$query = "SELECT Dept, CourseNumber, IsCorequisite FROM PrerequisiteInformation, CourseInstance WHERE PrerequisiteInformation.CourseID=CourseInstance.CourseID AND CourseInstance.ID='$courseInstanceID' ORDER BY IsCorequisite ASC;";
$result = mysql_query($query, $con);
if (mysql_num_rows($result) != 0)
{
	$first = true;
	$prevCoreq = false;
	while ($row = mysql_fetch_array($result))
	{
		if ($first AND ($row['IsCorequisite'] == 0))
		{
			print '<h3>Prerequisites</h3><ul>';
		}
		
		if (!$prevCoreq AND ($row['IsCorequisite'] == 1))
		{
			if (!$first)
			{
				print '</ul>';
			}
			
			print '<h3>Corequisites</h3><ul>';
		}
		
		print '<li>' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</li>';
		$first = false;
	}
	
	print '</ul>';
}

if ($state == 'Finalized')
{
	print '<p>ABET information for this course has already been finalized and may no longer be revised. If you need to fix an error, please contact <a href="mailto:foobar@gmail.com">foobar</a>.<p>';
}

if (isset($_REQUEST['error']))
{
	switch ($_REQUEST['error'])
	{
	case 0:
		print 'CLO Updated Successfully.';
		break;
		
	case 3:
		print 'You must provide how/where the outcome is assessed before approving.';
		break;
		
	case 4:
		print 'You must provide the satisfactory score before approving.';
		break;
	
	case 5:
		print 'An error occurred while submitting changes to the server.';
		break;
	}
}

print '<h2>Course Learning Outcomes (CLOs)</h2>';

if ($state != 'Finalized')
{
	print '<form action="';
}

switch ($state)
{
case 'Sent':
case 'Viewed':
case 'Approved':
	print 'approve.php';
	break;

case 'Ready':
	print 'finalize.php';
	break;
}

if ($state != 'Finalized')
{
	print '?courseInstanceID=' . $courseInstanceID . '" method="POST">';
}

print '<table><thead><tr>';
print '<th>Number</th>';
print '<th>Title and Description</th>';
print '<th>ABET Outcomes</th>';
print '<th>How/Where Assessed*</th>';
print '<th>Mean Score</th>';
print '<th>Median Score</th>';
print '<th>High Score</th>';
print '<th>Satisfactory Score**</th>';
print '</tr></thead><tbody>';
	
$query = "SELECT * FROM CourseInstanceCLOInformation WHERE CourseInstanceID='$courseInstanceID';";
$result = mysql_query($query, $con);

$i = 0;
while ($row = mysql_fetch_array($result))
{
	if (($i % 2) == 1)
	{
		print '<tr class="alt">';
	}
	else
	{
		print '<tr>';
	}
	
	print '<td>' . $row['CLONumber'] . '</td>';
	print '<td>' . $row['Description'] . '</td>';
	print '<td>' . $row['Outcomes'] . '</td>';
	print '<td>' . $row['Assessed'] . '</td>';
	print '<td>' . $row['MeanScore'] . '%</td>';
	
	if ($row['MedianScore'] == '')
	{
		print '<td>N/A';
	}
	else
	{
		print '<td>' . $row['MedianScore'] . '%';
	}
	print '</td>';
	
	print '<td>' . $row['HighScore'] . '%</td>';
	print '<td>' . $row['SatisfactoryScore'] . '%</td>';
	
	switch ($state)
	{
	case 'Sent':
	case 'Viewed':
	case 'Approved':
		if (($i % 2) == 1)
		{
			print '<tr class="alt">';
		}
		else
		{
			print '<tr>';
		}
		print '<td>&nbsp;</td>';
		print '<td>Request change</td>';
		print '<td>&nbsp;</td>';
		print '<td><input type="text" name="assessed[' . $row['CLONumber'] . ']" /></td>';
		print '<td>Locked until end of term.</td>';
		print '<td>Locked until end of term.</td>';
		print '<td>Locked until end of term.</td>';
		print '<td><input type="text" name="satisfactory[' . $row['CLONumber'] . ']" /></td>';
		print '</tr>';
		break;
	case 'Ready':
		if (($i % 2) == 1)
		{
			print '<tr class="alt">';
		}
		else
		{
			print '<tr>';
		}
		print '<td>&nbsp;</td>';
		print '<td>Request change</td>';
		print '<td>&nbsp;</td>';
		print '<td><input type="text" name="assessed[' . $row['CLONumber'] . ']" /></td>';
		print '<td><input type="text" name="mean[' . $row['CLONumber'] . ']" /></td>';
		print '<td><input type="text" name="median[' . $row['CLONumber'] . ']" /></td>';
		print '<td><input type="text" name="high[' . $row['CLONumber'] . ']" /></td>';
		print '<td><input type="text" name="satisfactory[' . $row['CLONumber'] . ']" /></td>';
		print '</tr>';
		break;
	case 'Finalized':
		break;
	}
	
	$i++;
}
	
print '</tbody></table>';

switch ($state)
{
case 'Sent':
case 'Viewed':
	print '<input type="submit" value="Approve" />';
	break;

case 'Approved':
	print '<input type="submit" value="Update" />';
	break;

case 'Ready':
	print '<input type="submit" value="Finalize" />';
	break;
}

if ($state != 'Finalized')
{
	print '</form>';
}

print '<h2>Comments</h2>';

if ($state != 'Finalized')
{
	print '<form action="comment.php?courseInstanceID=' . $courseInstanceID . '" method="POST">';
}

// CommentPrep //
print "<h3>Did the students in this course seem to have the preparation you expected? Describe any problems you observed in their preparation:</h3>";
print '<textarea name="prep" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $prep;

print '</textarea>';

// CommentPrepActions //
print "<h3>Actions taken (if any) in response to the students' level of preparation:</h3>";
print '<textarea name="prepActions" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $prepActions;

print '</textarea>';

// CommentChanges //
print "<h3>What other changes did you make compared with the last time this course was taught?</h3>";
print '<textarea name="changes" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $changes;

print '</textarea>';

// CommentCLO //
print "<h3>Are there any other changes you recommend for the way that the CLOs are covered and/or assessed?</h3>";
print '<textarea name="clo" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $clo;

print '</textarea>';

// CommentRecs //
print "<h3>Do you recommend any other changes for this course?</h3>";
print '<textarea name="recs" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $recs;

print '</textarea>';

if ($state != 'Finalized')
{
	print '<input type="submit" value="Submit Comments" />';
	print '</form>';
}

mysql_close($con);

?>

</body>
</html>
