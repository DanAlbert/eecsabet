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

?>
<h2>Remove Prerequisites</h2>
<form action="remove.php?courseID=<?php echo $courseID; ?>" method="POST">
	<table>
		<thead>
			<tr>
				<th>Remove</th>
				<th>Prerequisite Course</th>
				<th>Corequisite</th>
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
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" />
</form>

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
	
	mysql_close($con);
	
	?>
	</select>
	<br />
	<input id="isCorequisite" type="checkbox" name="isCorequisite" />
	<label for="isCorequisite">Is Corequisite</label>
	
	<input type="submit" value="Create New CLO" />
</form>

</body>
</html>
