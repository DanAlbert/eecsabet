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
if ($state == 'Finalized')
{
	print '<p>ABET information for this course has already been finalized and may no longer be revised. If you need to fix an error, please contact <a href="mailto:foobar@gmail.com">foobar</a>.<p>';
}

mysql_close($con);

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

?>

<h2>Course Learning Outcomes (CLOs)</h2>
<?php

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

?>

<table>
	<thead>
		<tr>
			<th>Number</th>
			<th>Title and Description</th>
			<th>ABET Outcomes</th>
			<th>How/Where Assessed*</th>
			<th>Mean Score</th>
			<th>Median Score</th>
			<th>High Score</th>
			<th>Satisfactory Score**</th>
		</tr>
	</thead>
	<tbody>
	
	<?php
	
	$hostname = 'mysql.gingerhq.net';
	$username = 'eecsabet';
	$password = 'hP5fRjZbZ6KcL7MU';
	$database = 'eecsabet';

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
	
	$query = "SELECT * FROM CourseInstanceCLOInformation WHERE CourseInstanceID='$courseInstanceID';";
	$result = mysql_query($query, $con);
	
	while ($row = mysql_fetch_array($result))
	{
		print '<tr>';
		print '<td>' . $row['CLONumber'] . '</td>';
		print '<td>' . $row['Description'] . '</td>';
		print '<td>' . $row['Outcomes'] . '</td>';
		print '<td>' . $row['Assessed'];
		if ($state != 'Finalized')
		{
			print '<br /><input type="text" name="assessed[' . $row['CLONumber'] . ']" />';
		}
		print '</td>';
		
		print '<td>' . $row['MeanScore'] . '%';
		if ($state == 'Ready')
		{
			print '<br /><input type="text" name="mean[' . $row['CLONumber'] . ']" />';
		}
		print '</td>';
		
		if ($row['MedianScore'] == '')
		{
			print '<td>N/A';
		}
		else
		{
			print '<td>' . $row['MedianScore'] . '%';
		}
		if ($state == 'Ready')
		{
			print '<br /><input type="text" name="median[' . $row['CLONumber'] . ']" />';
		}
		print '</td>';
		
		print '<td>' . $row['HighScore'] . '%';
		if ($state == 'Ready')
		{
			print '<br /><input type="text" name="high[' . $row['CLONumber'] . ']" />';
		}
		print '</td>';
		
		print '<td>' . $row['SatisfactoryScore'] . '%';
		if ($state != 'Finalized')
		{
			print '<br /><input type="text" name="satisfactory[' . $row['CLONumber'] . ']" />';
		}
		print '</td>';
	}
	
	mysql_close($con);
	?>
	
	</tbody>
</table>

<?php

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

?>

</body>
</html>
