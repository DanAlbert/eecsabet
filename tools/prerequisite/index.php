<!DOCTYPE html>
<html>
<head>
	<title>Edit Prerequisites</title>
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
	$sth = $dbh->prepare(
		"SELECT * " .
		"FROM CourseInformation " .
		"WHERE CourseID=:id");
	
	$sth->bindParam(':id', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$row = $sth->fetch();

print '<h1>Editing Prerequisites for ' . $row->Dept . ' ' .
	$row->CourseNumber . '</h1>';

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
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT ID, Dept, CourseNumber " .
			"FROM Course");
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		print '<option value="' . $row->ID . '">' . $row->Dept . ' ' .
			$row->CourseNumber . '</option>';
	}
	
	?>
	</select>
	<br />
	<input id="isCorequisite" type="checkbox" name="isCorequisite" />
	<label for="isCorequisite">Is Corequisite</label>
	
	<label>Alternatives:</label>
	<?php
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT * FROM " .
			"PrerequisiteInformation WHERE " .
			"CourseID=:id");
		
		$sth->bindParam(':id', $courseID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		print '<input id="alt[' . $row->PrerequisiteID . ']" type="checkbox" ' .
			'name="alt[' . $row->PrerequisiteID . ']" value="' .
			$row->PrerequisiteID . '"/>' . "\n";
		
		print '<label for="alt[' . $row->PrerequisiteID . ']">' . $row->Dept .
			' ' . $row->CourseNumber . '</label>' . "\n";
		
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
			
			try
			{
				$sth = $dbh->prepare(
					"SELECT * " .
					"FROM PrerequisiteInformation " .
					"WHERE CourseID=:id");
				
				$sth->bindParam(':id', $courseID);
				$sth->execute();
			}
			catch (PDOException $e)
			{
				die('PDOException: ' . $e->getMessage());
			}
			
			while ($row = $sth->fetch())
			{
				print '<tr>';
				print '<td><input type="checkbox" name="remove[' .
					$row->PrerequisiteID . ']" /></td>';
				
				print '<td>' . $row->Dept . ' ' . $row->CourseNumber . '</td>';
				print '<td>' . $row->IsCorequisite . '</td>';
				
				if ($row->Alternatives)
				{
					print '<td>' . $row->Alternatives . '</td>';
				}
				else
				{
					print '<td>None</td>';
				}
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" value="Submit" />
</form>
<br />
<a href="../terms-offered/index.php?courseID=<?php echo $courseID; ?>">
	Previous (Terms Offered)</a> |
<a href="../course-instance/index.php">Next (Course Instance)</a> or
	<a href="../instructor/index.php">Create an Instructor</a>

</body>
</html>
