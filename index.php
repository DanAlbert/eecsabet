<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<h1>Editing ABET Details for ECE 271</h1>
<h2>Spring 2008</h2>
<h3>Shuman, Matt</h3>

<?php

if (isset($_REQUEST['error']))
{
	switch ($_REQUEST['error'])
	{
	case 3:
		print 'You must provide how/where the outcome is assessed before approving.';
		break;
	case 4:
		print 'You must provide the satisfactory score before approving.';
		break;
	}
}

?>

<h2>Course Learning Outcomes (CLOs)</h2>
<table>
	<thead>
		<tr>
			<th>Number</th>
			<th>Title and Description</th>
			<th>ABET Outcomes</th>
			<th>How/Where Assessed*</th>
			<th>Mean Score</th>
			<th>Median Score</th>
			<th>High Score</th>
			<th>Satisfactory Score**</th>
			<th>Approve</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<form action="approve.php?courseID=271&outcomeID=1" method="POST">
				<td>1</td>
				<td><strong>Design</strong> combinational and sequential systems using integrated circuits available on the market.</td>
				<td>A,C,K</td>
				<td>Lab 1<br /><input type="text" name="assessed" /></td>
				<td>103%<br /><input type="text" name="mean" /></td>
				<td>N/A<br /><input type="text" name="median" /></td>
				<td>130%<br /><input type="text" name="high" /></td>
				<td>80%<br /><input type="text" name="satisfactory" /></td>
				<td><input type="submit" value="Approve" /></td>
			</form>
		</tr>
		<tr>
			<form action="approve.php?courseID=271&outcomeID=2" method="POST">
				<td>2</td>
				<td><strong>Implement and test</strong> the designed circuits using current laboratory equipment and testing techniques.</td>
				<td>A,C,K</td>
				<td>Lab 3<br /><input type="text" name="assessed" /></td>
				<td>94%<br /><input type="text" name="mean" /></td>
				<td>N/A<br /><input type="text" name="median" /></td>
				<td>138%<br /><input type="text" name="high" /></td>
				<td>80%<br /><input type="text" name="satisfactory" /></td>
				<td><input type="submit" value="Approve" /></td>
			</form>
		</tr>
		<tr>
			<form action="approve.php?courseID=271&outcomeID=3" method="POST">
				<td>3</td>
				<td><strong>Develop</strong> a small project, which usually consists of the design of a digital controller. The controller specification is provided in text form, and the students are exposed to all design phases: formal specification, design of gate networks, implementation, and testing.</td>
				<td>A,B,C,E,K,O,Q</td>
				<td>Project<br /><input type="text" name="assessed" /></td>
				<td>83%<br /><input type="text" name="mean" /></td>
				<td>N/A<br /><input type="text" name="median" /></td>
				<td>150%<br /><input type="text" name="high" /></td>
				<td>80%<br /><input type="text" name="satisfactory" /></td>
				<td><input type="submit" value="Approve" /></td>
			</form>
		</tr>
		<tr>
			<form action="approve.php?courseID=271&outcomeID=4" method="POST">
				<td>4</td>
				<td><strong>Report</strong> the develpment of the experiments and laboratory results in written form.</td>
				<td>G</td>
				<td>Project<br /><input type="text" name="assessed" /></td>
				<td>83%<br /><input type="text" name="mean" /></td>
				<td>N/A<br /><input type="text" name="median" /></td>
				<td>150%<br /><input type="text" name="high" /></td>
				<td>80%<br /><input type="text" name="satisfactory" /></td>
				<td><input type="submit" value="Approve" /></td>
			</form>
		</tr>
	</tbody>
</table>

</body>
</html>
