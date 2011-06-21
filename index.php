<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php

$hostname = 'mysql.gingerhq.net';
$username = 'eecsabet';
$password = 'hP5fRjZbZ6KcL7MU';
$database = 'eecsabet';

$courseInstanceID = $_REQUEST['courseInstanceID'];

$con = mysql_connect($hostname, $username, $password);
if (!$con)
{
	mysql_close($con);
	die('Unable to connect to database: ' . mysql_error());
}

if (!mysql_select_db($database))
{
	mysql_close($con);
	die('Unable to select database: ' . mysql_error());
}

$query = "SELECT * FROM CourseInstanceInformation WHERE CourseInstanceID=$courseInstanceID;";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

print '<h1>Editing ABET Details for ' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</h1>';
print '<h2>' . $row['Term'] . ' ' . $row['Year'] . '</h2>';
print '<h3>' . $row['Name'] . '</h3>';

$state = $row['State'];
$comments = $row['Comments'];

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

print '<textarea name="comments" cols="60" rows="10"';
if ($state == 'Finalized')
{
	print ' disabled="disabled"';
}
print '>';

print $comments;

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
