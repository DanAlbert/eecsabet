<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<!--<h1>Editing ABET Details for ECE 271</h1>
<h2>Spring 2008</h2>
<h3>Shuman, Matt</h3>-->

<?php

$hostname = 'mysql.gingerhq.net';
$username = 'eecsabet';
$password = 'hP5fRjZbZ6KcL7MU';
$database = 'eecsabet';

$courseInstanceID = 1;

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
			<th>State</th>
			<th>Approve</th>
		</tr>
	</thead>
	<tbody>
	
	<?php
	
	$hostname = 'mysql.gingerhq.net';
	$username = 'eecsabet';
	$password = 'hP5fRjZbZ6KcL7MU';
	$database = 'eecsabet';
	
	$courseInstanceID = 1;

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
		print '<tr><form action="';
		
		switch ($row['State'])
		{
		case 'Sent':
		case 'Viewed':
		case 'Approved':
			print 'approve.php';
			break;
		
		case 'Ready':
		case 'Finalized':
			print 'finalize.php';
			break;
		}
		
		print '?courseInstanceID=' . $courseInstanceID . '&outcomeID=' . $row['CLONumber'] . '" method="POST">';
		print '<td>' . $row['CLONumber'] . '</td>';
		print '<td>' . $row['Description'] . '</td>';
		print '<td>' . $row['Outcomes'] . '</td>';
		print '<td>' . $row['Assessed'] . '<br /><input type="text" name="assessed" /></td>';
		
		print '<td>' . $row['MeanScore'] . '%';
		if (($row['State'] == 'Ready') OR ($row['State'] == 'Finalized'))
		{
			print '<br /><input type="text" name="mean" />';
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
		if (($row['State'] == 'Ready') OR ($row['State'] == 'Finalized'))
		{
			print '<br /><input type="text" name="median" />';
		}
		print '</td>';
		
		print '<td>' . $row['HighScore'] . '%';
		if (($row['State'] == 'Ready') OR ($row['State'] == 'Finalized'))
		{
			print '<br /><input type="text" name="high" />';
		}
		print '</td>';
		
		print '<td>' . $row['SatisfactoryScore'] . '%<br /><input type="text" name="satisfactory" /></td>';
		
		print '<td>' . $row['State'] . '</td>';
		
		switch ($row['State'])
		{
		case 'Sent':
		case 'Viewed':
			print '<td><input type="submit" value="Approve" /></td>';
			break;
		
		case 'Approved':
		case 'Finalized':
			print '<td><input type="submit" value="Update" /></td>';
			break;
		
		case 'Ready':
			print '<td><input type="submit" value="Finalize" /></td>';
			break;
		}
		print '</form></tr>';
	}
	
	mysql_close($con);
	?>
	
	</tbody>
</table>

</body>
</html>
