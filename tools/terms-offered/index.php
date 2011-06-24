<!DOCTYPE html>
<html>
<head>
	<title>Edit Terms Offered</title>
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

$query = "SELECT Summer, Fall, Winter, Spring FROM TermsOffered WHERE CourseID='$courseID';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

$summer = $row['Summer'];
$fall = $row['Fall'];
$winter = $row['Winter'];
$spring = $row['Spring'];

$query = "SELECT * FROM CourseInformation WHERE CourseID='$courseID';";
$result = mysql_query($query, $con);
$row = mysql_fetch_array($result);

print '<h1>Editing Terms that ' . $row['Dept'] . ' ' . $row['CourseNumber'] . ' is Offered</h1>';

mysql_close($con);

?>

<h2>Terms Offered</h2>
<form action="submit.php?courseID=<?php echo $courseID; ?>" method="POST">
	<input id="summer" type="checkbox" name="summer" <?php if ($summer) { echo 'checked="checked" '; } ?>/>
	<label for="summer">Summer</label>
	<br />
	<input id="fall" type="checkbox" name="fall" <?php if ($fall) { echo 'checked="checked" '; } ?>/>
	<label for="fall">Fall</label>
	<br />
	<input id="winter" type="checkbox" name="winter" <?php if ($winter) { echo 'checked="checked" '; } ?>/>
	<label for="fawinter">Winter</label>
	<br />
	<input id="spring" type="checkbox" name="spring" <?php if ($spring) { echo 'checked="checked" '; } ?>/>
	<label for="spring">Spring</label>
	
	<input type="submit" value="Submit" />
</form>

</body>
</html>
