<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script type="text/javascript"
		src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
	</script>
	
	<script type="text/javascript">
	
	function add(tableID)
	{
		var id = $("table#" + tableID + " tbody tr:last-child").attr('id');
		var rowNum;
		
		if (typeof id === 'undefined')
		{
			rowNum = 0;
		}
		else
		{
			var parts = id.split('-');	
			rowNum = parseInt(parts[1]) + 1;
		}
		
		var rowID = tableID + '-' + rowNum;
		
		var delButton = '<button type="button" onclick="remove(' + tableID + 
			', ' + rowNum + ')">Remove</button>';
		
		var state = $("table#" + tableID).attr('title');
		
		var methodName = 'method[' + tableID + '][' + rowNum + ']';
		var meanName = 'mean[' + tableID + '][' + rowNum + ']';
		var medianName = 'median[' + tableID + '][' + rowNum + ']';
		var highName = 'high[' + tableID + '][' + rowNum + ']';
		var satisfactoryName = 'satisfactory[' + tableID + '][' + rowNum + ']';
		var attainedName = 'attained[' + tableID + '][' + rowNum + ']';
		
		var row =
			'<tr id="' + rowID + '">' +
			'<td><input type="text" name="' + methodName + '" /></td>';
		
		if (state == 'Ready')
		{
			row +=
				'<td><input type="text" name="' + meanName + '" /></td>' +
				'<td><input type="text" name="' + medianName + '" /></td>' +
				'<td><input type="text" name="' + highName + '" /></td>';
		}
		else
		{
			row +=
				'<td>Locked until end of term.</td>' +
				'<td>Locked until end of term.</td>' +
				'<td>Locked until end of term.</td>';
		}
		
		row +=
			'<td><input type="text" name="' + satisfactoryName + '" /></td>';
		
		if (state == 'Ready')
		{
			row += '<td><input type="text" name="' + attainedName + '" /></td>';
		}
		else
		{
			row += '<td>Locked until end of term.</td>';
		}
		
		row += '<td>' + delButton + '</td></tr>';
		
		$("table#" + tableID + " tbody").append(row);
	}
	
	function remove(tableID, rowNum)
	{
		var rowID = tableID + '-' + rowNum;
		$("table#" + tableID + " tbody tr#" + rowID).remove();
	}
	
	</script>
</head>
<body>

<a href="tools/index.php">Administrative Tools</a> |
<a href="readme.html">Help</a>
<br />
<?php

if (isset($_REQUEST['error']))
{
	print '<p class="error">';
	switch ($_REQUEST['error'])
	{
	case 0:
		print 'Information submitted successfully.';
		break;
		
	case 1:
		print 'You must complete all of the fields below before proceeding.';
		break;
	
	default:
		print 'Unknown error.';
		break;
	}
	print '</p>';
}

if (!isset($_REQUEST['courseInstanceID']))
{
	print '<p>No course instance ID provided. Please use the link provided to' .
		' you by email to access this page.</p>';
	print '<h2><Resend Email</h2>';
	print '<form action="resend.php" method="POST"><label for="email">Email' .
		'</label><input id="email" type="text" name="email" />' .
		'<input type="submit" value="Resend Email" /></form>';
	return;
}

include_once 'debug.php';
require_once 'db.php';

$dbh = dbConnect();

$courseInstanceID = $_REQUEST['courseInstanceID'];

try
{
	$sth = $dbh->prepare(
		"SELECT * FROM CourseInstanceInformation " .
		"WHERE CourseInstanceID=:id");
	
	$sth->bindParam(':id', $courseInstanceID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
$row = $rows[0];

if ($row->State == 'Finalized')
{
	print '<h3 class="notice">You have completed all course information. You ' .
		'may now close this window.</h3>';
}

$termID = $row->TermID;
$year = floor($termID / 100);
$term = '';
switch ($termID - ($year * 100))
{
case 0:
	$term = 'Summer';
	$year -= 1;
	break;
case 1:
	$term = 'Fall';
	$year -= 1;
	break;
case 2:
	$term = 'Winter';
	break;
case 3:
	$term = 'Spring';
	break;
}

$dept = $row->Dept;

print '<h1>Editing ABET Details for ' . $dept . ' ' . $row->CourseNumber .
	' (' . $row->CreditHours . ')</h1>';

print '<h2>' . $row->Title . '</h2>';
print '<h2>' . $term . ' ' . $year . '</h2>';
print '<h3>' . $row->FirstName . ' ' . $row->LastName . '</h3>';
print '<h3>Catalog Description</h3>';
print '<p>' . $row->Description . '</p>';

$state = $row->State;
$prep = $row->CommentPrep;
$prepActions = $row->CommentPrepActions;
$changes = $row->CommentChanges;
$cloComment = $row->CommentCLO;
$recs = $row->CommentRecs;

try
{
	$sth = $dbh->prepare(
		"SELECT Dept, CourseNumber, IsCorequisite " .
		"FROM	PrerequisiteInformation AS PI, " .
				"CourseInstance AS CI " .
		"WHERE	PI.CourseID=CI.CourseID AND " .
				"CI.ID=:id " .
		"ORDER BY IsCorequisite ASC");
	$sth->bindParam(':id', $courseInstanceID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
if (sizeof($rows) > 0)
{
	$first = true;
	$prevCoreq = false;
	foreach ($rows as $row)
	{
		if ($first AND ($row->IsCorequisite == 0))
		{
			print '<h3>Prerequisites</h3><ul>';
		}
		
		if (!$prevCoreq AND ($row->IsCorequisite == 1))
		{
			if (!$first)
			{
				print '</ul>';
			}
			
			print '<h3>Corequisites</h3><ul>';
		}
		
		print '<li>' . $row->Dept . ' ' . $row->CourseNumber . '</li>';
		$first = false;
	}
	
	print '</ul>';
}

if ($state == 'Finalized')
{
	print '<p>ABET information for this course has already been finalized ' .
		'and may no longer be revised. If you need to fix an error, please ' .
		'contact <a href="mailto:someone@gmail.com">this person</a>.<p>';
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

try
{
	$sth = $dbh->prepare("CALL GetCourseInstanceCLOs(:id)");
	$sth->bindParam(':id', $courseInstanceID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$clos = $sth->fetchAll();
dbFlush($sth); // Flush stats result set
foreach ($clos as $clo)
{
	print '<h3>' . $clo->CLONumber . '.' . ' ' . $clo->Description .
			' (' . $clo->Outcomes . ')</h3>';
	
	printImprovementMessages($dbh, $dept, $clo->Outcomes);
	
	try
	{
		$sth = $dbh->prepare("CALL GetRecentCLOMetrics(:cloid, :term)");
		$sth->bindParam(':cloid', $clo->ID);
		$sth->bindParam(':term', $termID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$metrics = $sth->fetchAll();
	
	if (sizeof($metrics) > 0)
	{
		if ($state == 'Sent')
		{
			print '<h4>Recent Data</h4>';
		}
		else
		{
			print '<h4>Current Data</h4>';
		}
		
		print '<table class="no-border"><thead>';
		print '<tr><th>Assessment Method</th>';
		print '<th>Mean Score</th>';
		print '<th>Median Score</th>';
		print '<th>High Score</th>';
		print '<th>Satisfactory Score %</th>';
		print '<th>% Who Attained</th>';
		print '</thead><tbody>';
	}
	
	dbFlush($sth);
	foreach ($metrics as $metric)
	{
		print '<tr>';
		if ($metric->Method == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->Method . '</td>';
		}
		
		if ($metric->Mean == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->Mean . '%</td>';
		}
		
		if ($metric->Median == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->Median . '%</td>';
		}
		
		if ($metric->High == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->High . '%</td>';
		}
		
		if ($metric->Satisfactory == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->Satisfactory . '%</td>';
		}
		
		if ($metric->Attained == '')
		{
			print '<td>N/A</td>';
		}
		else
		{
			print '<td>' . $metric->Attained . '%</td>';
		}
		
		print '</tr>';
	}
	
	print '</tbody></table><br />';
	
	if ($state != 'Finalized')
	{
		print '<table title="' . $state . '" id="' . $clo->ID . '"><thead>';
		print '<tr><th>Assessment Method</th>';
		print '<th>Mean Score</th>';
		print '<th>Median Score</th>';
		print '<th>High Score</th>';
		print '<th>Satisfactory Score %</th>';
		print '<th>% Who Attained</th>';
		print '<th>Remove</th>';
		print '</thead><tbody>';
		
		switch ($state)
		{
		case 'Sent':
			print '<tr id="' . $clo->ID . '-0">';
			print '<td><input type="text" name="method[' . $clo->ID .
				'][0]" /></td>';
			
			print '<td>Locked until end of term.</td>';
			print '<td>Locked until end of term.</td>';
			print '<td>Locked until end of term.</td>';
			
			print '<td><input type="text" name="satisfactory[' . $clo->ID .
				'][0]" /></td>';
			
			print '<td>Locked until end of term.</td>';
			
			print '<td><button type="button" onclick="remove(' . $clo->ID .
				', 0)">Remove</button></td>';
			
			break;
			
		case 'Approved':
			$i = 0;
			foreach ($metrics as $metric)
			{
				print '<tr id="' . $clo->ID . '-' . $i . '">';
				print '<td><input type="text" name="method[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Method . '" /></td>';
				
				print '<td>Locked until end of term.</td>';
				print '<td>Locked until end of term.</td>';
				print '<td>Locked until end of term.</td>';
				
				print '<td><input type="text" name="satisfactory[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Satisfactory .
					'" /></td>';
			
				print '<td>Locked until end of term.</td>';
				
				print '<td><button type="button" onclick="remove(' . $clo->ID .
					', ' . $i . ')">Remove</button></td>';
				
				$i++;
			}
			break;
			
		case 'Ready':
			$i = 0;
			foreach ($metrics as $metric)
			{
				print '<tr id="' . $clo->ID . '-' . $i . '">';
				
				print '<td><input type="text" name="method[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Method . '" /></td>';
					
				print '<td><input type="text" name="mean[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Mean . '" /></td>';
				
				print '<td><input type="text" name="median[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Median . '" /></td>';
					
				print '<td><input type="text" name="high[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->High . '" /></td>';
				
				print '<td><input type="text" name="satisfactory[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Satisfactory .
					'" /></td>';
				
				print '<td><input type="text" name="attained[' . $clo->ID .
					'][' . $i . ']" value="' . $metric->Attained .
					'" /></td>';
				
				print '<td><button type="button" onclick="remove(' . $clo->ID .
					', ' . $i . ')">Remove</button></td>';
				
				$i++;
			}
			break;
		}
		
		print '</tr>';
		
		print '</tbody></table>';
		print '<button type="button" onclick="add(' . $clo->ID .
			')">Add Assessment</button>';
	}
}
	


print '<h2>Comments</h2>';

if (($state == 'Ready') OR ($state == 'Finalized'))
{
	// CommentPrep //
	print "<h3>Did the students in this course seem to have the preparation " .
		"you expected? Describe any problems you observed in their " .
		"preparation:</h3>";
		
	print '<textarea name="prep" cols="60" rows="10"';
	if ($state != 'Ready')
	{
		print ' disabled="disabled"';
	}
	print '>';

	print $prep;

	print '</textarea>';

	// CommentPrepActions //
	print "<h3>Actions taken (if any) in response to the students' level of " .
		"preparation:</h3>";
		
	print '<textarea name="prepActions" cols="60" rows="10"';
	if ($state != 'Ready')
	{
		print ' disabled="disabled"';
	}
	print '>';

	print $prepActions;

	print '</textarea>';

	// CommentChanges //
	print "<h3>What other changes did you make compared with the last time " .
		"this course was taught?</h3>";
		
	print '<textarea name="changes" cols="60" rows="10"';
	if ($state != 'Ready')
	{
		print ' disabled="disabled"';
	}
	print '>';

	print $changes;

	print '</textarea>';

	// CommentCLO //
	print "<h3>Are there any other changes you recommend for the way that " .
		"the CLOs are covered and/or assessed?</h3>";
		
	print '<textarea name="clo" cols="60" rows="10"';
	if ($state != 'Ready')
	{
		print ' disabled="disabled"';
	}
	print '>';

	print $cloComment;

	print '</textarea>';
}

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

function printImprovementMessages($dbh, $dept, $outcomeString)
{
	$outcomes = array();
	$items = str_split($outcomeString);
	foreach ($items as $item)
	{
		if (ctype_alpha($item))
		{
			$outcomes[] = $item;
		}
	}
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT	Description,
					Improvement
			FROM	Outcomes
			WHERE 	Improvement<>'' AND
					Outcomes.Dept=:dept AND
					(	Outcomes.Outcome=UPPER(:outcome) OR
						Outcomes.Outcome=LOWER(:outcome))");
		
		$sth->bindParam(':dept', $dept);
		$sth->bindParam(':outcome', $outcome);
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	$first = true;
	foreach ($outcomes as $outcome)
	{
		try
		{
			$sth->execute();
		}
		catch (PDOException $e)
		{
			die('PDOException: ' . $e->getMessage());
		}
		
		$rows = $sth->fetchAll();
		if (sizeof($rows) > 0)
		{
			if ($first)
			{
				print '<h4>Curriculum Improvement Messages</h4>';
				print '<hr />';
			}
			foreach ($rows as $row)
			{
				print "<h5>{$outcome}. {$row->Description}</h5>";
				print $row->Improvement;
				print '<hr />';
			}
			
			$first = false;
		}
	}
}

?>

</body>
</html>
