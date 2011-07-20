<?php

include_once '../../debug.php';
require_once '../../db.php';

$dept = $_POST['dept'];
$outcome = $_POST['outcome'];

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare("CALL GetProgramOutcomeInfo(:dept, :outcome)");
	
	$sth->bindParam(':dept', $dept);
	$sth->bindParam(':outcome', $outcome);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$row = $sth->fetch();

$significant = 'None';
$courses = 'None';
$methods = 'None';

if ($row !== false)
{
	$significant = $row->SignificantCourses;
	$courses = $row->Courses;
	$methods = $row->Methods;
}

try
{
	$sth = $dbh->prepare("CALL GetPerformanceCriteria(:dept, :outcome)");
	
	$sth->bindParam(':dept', $dept);
	$sth->bindParam(':outcome', $outcome);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
$size = sizeof($rows);

$firstCriterion = 'None';
if ($size > 0)
{
	$firstCriterion = $rows[0]->Criterion;
}

print '<table><thead><tr>';
print '<th>Courses where Included as Significant</th>';
print '<th>Performance Criteria</th>';
print '<th>Courses where Assessed</th>';
print '<th>Assessment Method</th>';
print '</tr></thead><tbody>';

if ($size > 1)
{
	print '<tr>';
	print '<td rowspan="' . $size . '">' . $significant . '</td>';
	print '<td>' . $firstCriterion . '</td>';
	print '<td rowspan="' . $size . '">' . $courses . '</td>';
	print '<td rowspan="' . $size . '">' . $methods . '</td>';
	print '</tr>';
	
	for ($i = 1; $i < $size; $i++)
	{
		if ($i % 2)
		{
			print '<tr class="alt">';
		}
		else
		{
			print '<tr>';
		}
		
		print '<td>' . $rows[$i]->Criterion . '</td></tr>';
	}
}
else
{
	print '<tr>';
	print '<td>' . $significant . '</td>';
	print '<td>' . $firstCriterion . '</td>';
	print '<td>' . $courses . '</td>';
	print '<td>' . $methods . '</td>';
	print '</tr>';
}

print '</table>';

?>