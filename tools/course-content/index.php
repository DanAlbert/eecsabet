<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
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

print '<h1>Editing Course Content for ' . $row['Dept'] . ' ' . $row['CourseNumber'] . '</h1>';

?>
<h2>Remove Content</h2>
<?php

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 2))
{
	print '<p class="error">Nothing to remove.</p>';
}

?>
<form action="remove.php?courseID=<?php echo $courseID; ?>" method="POST">
	<table>
		<thead>
			<tr>
				<th>Remove</th>
				<th>Content</th>
			</tr>
		</thead>
		<tbody>
			<?php
			
			$query = "SELECT ID, Content FROM CourseContent WHERE CourseID='$courseID';";
			$result = mysql_query($query, $con);
			
			while ($row = mysql_fetch_array($result))
			{
				print '<tr>';
				print '<td><input type="checkbox" name="remove[' . $row['ID'] . ']" /></td>';
				print '<td>' . $row['Content'] . '</td>';
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" />
</form>

<h2>Add New Content Entry</h2>
<form action="create.php?courseID=<?php echo $courseID; ?>" method="POST">
	<label for="content">Content</label>
	<input id="content" type="text" name="content" />
	
	<input type="submit" value="Create New Content" />
</form>

</body>
</html>
