<!DOCTYPE html>
<html>
<head>
	<title>Edit Terms Offered</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>
<?php

include_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];

print '<a href="../index.php?courseID=' . $courseID . '">' .
	'Return to Adminstration Page</a>';

try
{
	$sth = $dbh->prepare(
		"SELECT Summer, Fall, Winter, Spring " .
		"FROM TermsOffered " .
		"WHERE CourseID=:id");
	
	$sth->bindParam(':id', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$row = $sth->fetch();

$summer = $row->Summer;
$fall = $row->Fall;
$winter = $row->Winter;
$spring = $row->Spring;

try
{
	$sth = $dbh->prepare("SELECT * FROM CourseInformation WHERE CourseID=:id");
	$sth->bindParam(':id', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

$row = $sth->fetch();

print '<h1>Editing Terms that ' . $row->Dept . ' ' . $row->CourseNumber .
	' is Offered</h1>';

?>

<h2>Terms Offered</h2>
<form action="submit.php?courseID=<?php echo $courseID; ?>" method="POST">
	<input id="summer" type="checkbox" name="summer"
		<?php if ($summer) { echo 'checked="checked" '; } ?>/>
	<label for="summer">Summer</label>
	<br />
	<input id="fall" type="checkbox" name="fall" 
		<?php if ($fall) { echo 'checked="checked" '; } ?>/>
	<label for="fall">Fall</label>
	<br />
	<input id="winter" type="checkbox" name="winter" 
		<?php if ($winter) { echo 'checked="checked" '; } ?>/>
	<label for="fawinter">Winter</label>
	<br />
	<input id="spring" type="checkbox" name="spring" 
		<?php if ($spring) { echo 'checked="checked" '; } ?>/>
	<label for="spring">Spring</label>
	
	<input type="submit" value="Submit" />
</form>

</body>
</html>
