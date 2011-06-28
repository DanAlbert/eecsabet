<!DOCTYPE html>
<html>
<head>
	<title>Edit Prerequisites</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>
<?php

require_once '../../db.php';
	
$con = dbConnect();
if (!con)
{
	die('Unable to connect to database: ' . mysql_error());
}

$courseID = mysql_real_escape_string($_REQUEST['courseID']);

print '<a href="../index.php?courseID=' . $courseID . '">Return to Adminstration Page</a>';

$query = "SELECT * FROM CourseInformation WHERE CourseID='$courseID';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

print '<h1>Editing Prerequisites for ' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</h1>';

?>
<h2>Add a New Prerequisite</h2>
<?php

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 1))
{
	print '<p class="error">You must provide at least one ABET outcome for a CLO.</p>';
}

?>
<form action="create.php?courseID=<?php echo $courseID; ?>" method="POST">
	<label for="prerequisiteID">Prerequisite Course</label>
	<select name="prerequisiteID">
	<?php
	
	$query = "SELECT ID, Dept, CourseNumber FROM Course;";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		print '<option value="' . $row['ID'] . '">' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</option>';
	}
	
	?>
	</select>
	<br />
	<input id="isCorequisite" type="checkbox" name="isCorequisite" />
	<label for="isCorequisite">Is Corequisite</label>
	
	<label>Alternatives:</label>
	<?php
	
	$query = "SELECT * FROM PrerequisiteInformation WHERE CourseID='$courseID'";
	$result = mysql_query($query, $con);
	
	while ($row = mysql_fetch_array($result))
	{
		print '<input id="alt[' . $row['PrerequisiteID'] . ']" type="checkbox" name="alt[' . $row['PrerequisiteID'] . ']" value="' . $row['PrerequisiteID'] . '"/>' . "\n";
		print '<label for="alt[' . $row['PrerequisiteID'] . ']">' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</label>' . "\n";
		print '<br />' . "\n";
	}
	
	?>
	<input type="submit" value="Create New Prerequisite" />
</form>

<h2>Remove Prerequisites</h2>
<form action="remove.php?courseID=<?php echo $courseID; ?>" method="POST">
	<table>
		<thead>
			<tr>
				<th>Remove</th>
				<th>Prerequisite Course</th>
				<th>Corequisite</th>
				<th>Alternatives</th>
			</tr>
		</thead>
		<tbody>
			<?php
			
			$query = "SELECT * FROM PrerequisiteInformation WHERE CourseID='$courseID'";
			$result = mysql_query($query, $con);
			
			while ($row = mysql_fetch_array($result))
			{
				print '<tr>';
				print '<td><input type="checkbox" name="remove[' . $row['PrerequisiteID'] . ']" /></td>';
				print '<td>' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</td>';
				print '<td>' . $row['IsCorequisite'] . '</td>';
				if ($row['Alternatives'])
				{
					print '<td>' . $row['Alternatives'] . '</td>';
				}
				else
				{
					print '<td>None</td>';
				}
				print '</tr>';
			}
	
			mysql_close($con);
			
			?>
		</tbody>
	</table>
	<input type="submit" />
</form>

</body>
</html>
