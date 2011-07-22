<?php

include_once '../../debug.php';
require_once '../../db.php';

$id = $_POST['id'];
$term = $_POST['term'];
$email = $_POST['email'];

if (($id == '') or ($term == '') or ($email == ''))
{
	return;
}

$dbh = dbConnect();

try
{
	$sth = $dbh->prepare("CALL GetCourseComments(:id, :term, :email)");
	
	$sth->bindParam(':id', $id);
	$sth->bindParam(':term', $term);
	$sth->bindParam(':email', $email);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$comments = $sth->fetch();
dbFlush($sth);

try
{
	$sth = $dbh->prepare("CALL GetCourseAssessment(:id, :term, :email)");
	
	$sth->bindParam(':id', $id);
	$sth->bindParam(':term', $term);
	$sth->bindParam(':email', $email);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$rows = $sth->fetchAll();
dbFlush($sth);

print "<p>Did the students in this course seem to have the preparation you " .
	"expected? Describe any problems you observed in their preparation:</p>";
print "<em>{$comments->Prep}</em>";

print '<p>Actions taken (if any) based on above observations:</p>';
print "<em>{$comments->PrepActions}</em>";

print "<p>Assessment of student's achievemnt of course learning objectives. " .
	"Last column refers to the percentage of students who satisfied the " .
	"learning objective.";

print '<table><thead><tr>';
print '<th>Learning Objective</th>';
print '<th>How Assessed</th>';
print '<th>Satisfactory Level</th>';
print '<th>% Who Attained</th>';
print '</tr></thead><tbody>';

foreach ($rows as $row)
{
	print '<tr>';
	print '<td>' . $row->CLO . '</td>';
	print '<td>' . nl2br($row->Methods) . '</td>';
	print '<td>' . nl2br($row->Satisfactory) . '</td>';
	print '<td>' . nl2br($row->Attained) . '</td>';
	print '</tr>';
}

print '</table>';

print "<p>Do course learning objectives need attention? If so, how will " .
	"changes be addressed?</p>";
print "<em>{$comments->CLO}</em>";

?>