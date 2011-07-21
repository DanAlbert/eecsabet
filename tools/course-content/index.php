<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>
<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];

print '<a href="../index.php?courseID=' . $courseID . '">Return to ' .
	'Adminstration Page</a> | <a href="readme.html">Help</a>';

try
{
	$sth = $dbh->prepare("SELECT * FROM CourseInformation WHERE CourseID=:id");
	$sth->bindParam(':id', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getmessage());
}

$row = $sth->fetch();

print '<h1>Editing Course Content for ' . $row->Dept . ' ' . 
	$row->CourseNumber . '</h1>';

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
			
			try
			{
				$sth = $dbh->prepare(
					"SELECT ID, Content " .
					"FROM CourseContent " .
					"WHERE CourseID=:id");
				
				$sth->bindParam(':id', $courseID);
				$sth->execute();
			}
			catch (PDOException $e)
			{
				die('PDOException: ' . $e->getmessage());
			}
			
			while ($row = $sth->fetch())
			{
				print '<tr>';
				print '<td><input type="checkbox" name="remove[' . $row->ID . 
					']" /></td>';
				
				print '<td>' . $row->Content . '</td>';
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" value="Submit" />
</form>

<h2>Add New Content Entry</h2>
<form action="create.php?courseID=<?php echo $courseID; ?>" method="POST">
	<label for="content">Content</label>
	<input id="content" type="text" name="content" />
	
	<input type="submit" value="Create New Content" />
</form>
<br />
<a href="../clo/index.php?courseID=<?php echo $courseID; ?>">
	Previous (CLOs)</a> |
<a href="../learning-resources/index.php?courseID=<?php echo $courseID; ?>">
	Next (Learning Resources)</a>

</body>
</html>
