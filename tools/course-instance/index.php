<!DOCTYPE html>
<html>
<head>
	<title>Create a new Course Instance</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>

<form action="create.php" method="POST">
	
	<?php
	
	require_once '../../db.php';
	
	$con = dbConnect();
	if (!con)
	{
		die('Unable to connect to database: ' . mysql_error());
	}
	
	print '<label for="course">Course</label>';
	print '<select id="course" name="course">';
	
	$query = "SELECT ID, Dept, CourseNumber FROM Course;";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		print '<option value="' . $row['ID'] . '">' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</option>';
	}
	print '</select>';
	
	print '<label for="instructor">Instructor</label>';
	print '<select id="instructor" name="instructor">';
	
	$query = "SELECT Name, Email FROM Instructor;";
	$result = mysql_query($query, $con);
	while ($row = mysql_fetch_array($result))
	{
		print '<option value="' . $row['Email'] . '">' . $row['Name'] . '</option>';
	}
	print '</select>';
	
	mysql_close($con);
	
	?>

	<label for="date">Term</label>
	<select id="date" name="term">
		<option value="00">Summer</option>
		<option value="01">Fall</option>
		<option value="02">Winter</option>
		<option value="03">Spring</option>
	</select>
	
	<?php
	
	print '<select name="year">';
	$date = getdate();
	foreach (range(2005, $date['year']) as $year)
	{
		print '<option value="' . $year . '"';
		if ($year == $date['year'])
		{
			print ' selected="selected"';
		}
		print '>' . $year . '</option>';
	}
	print '</select>';
	
	?>
	
	<input type="submit" value="Create New Course Instance" />
</form>

</body>
</html>
