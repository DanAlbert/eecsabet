<!DOCTYPE html>
<html>
<head>
	<title>Edit CLOs</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript">
	
	function moveUp(num)
	{
		var currentVal = parseInt($("table#edit tr#" + num + " input[type=hidden]").val());
		
		if (currentVal == 1)
		{
			return;
		}
		
		// Move up
		$("table#edit tr#" + num + " span").html(currentVal - 1);
		$("table#edit tr#" + num + " input[type=hidden]").val(currentVal - 1);
		
		// Move previous entry down
		$("table#edit tr#" + num).prev().find("span").first().html(currentVal);
		$("table#edit tr#" + num).prev().find("input[type=hidden]").first().val(currentVal);
		
		// Swap
		$("table#edit tr#" + num).attr("id", "temp" + num);
		$("<tr id=\"" + num + "\">" + $("table#edit tr#temp" + num).html() + "</tr>").insertBefore($("table#edit tr#temp" + num).prev());
		$("table#edit tr#temp" + num).remove();
	}
	
	function moveDown(num)
	{
		var currentVal = parseInt($("table#edit tr#" + num + " input[type=hidden]").val());
		var maxVal = parseInt($("table#edit tr").last().find("input[type=hidden]").first().val());
		
		if (currentVal == maxVal)
		{
			return;
		}
		
		// Move down
		$("table#edit tr#" + num + " span").html(currentVal + 1);
		$("table#edit tr#" + num + " input[type=hidden]").val(currentVal + 1);
		
		// Move next entry up
		$("table#edit tr#" + num).next().find("span").first().html(currentVal);
		$("table#edit tr#" + num).next().find("input[type=hidden]").first().val(currentVal);
		
		// Swap
		$("table#edit tr#" + num).attr("id", "temp" + num);
		$("<tr id=\"" + num + "\">" + $("table#edit tr#temp" + num).html() + "</tr>").insertAfter($("table#edit tr#temp" + num).next());
		$("table#edit tr#temp" + num).remove();
	}
	
	</script>
</head>
<body>
<?php

require_once '../../debug.php';
require_once '../../db.php';

$dbh = dbConnect();

$courseID = $_REQUEST['courseID'];

print '<a href="../index.php?courseID=' . $courseID . '">Return to Adminstration Page</a>';

try
{
	$sth = $dbh->prepare("SELECT * FROM CourseInformation WHERE CourseID=:id");
	$sth->bindParam(':id', $courseID);
	$sth->execute();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage);
}

$row = $sth->fetch();
print '<h1>Editing CLOs for ' . $row->Dept . ' ' . $row->CourseNumber . '</h1>';

?>
<h2>Remove CLOs</h2>
<?php

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 2))
{
	print '<p class="error">Nothing to remove.</p>';
}

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 4))
{
	print '<p class="error">Please don\'t do that, I\'m still fixing things.</p>';
}

?>
<form action="remove.php?courseID=<?php echo $courseID; ?>" method="POST">
	<table>
		<thead>
			<tr>
				<th>Remove</th>
				<th>Number</th>
				<th>Description</th>
				<th>Outcomes</th>
			</tr>
		</thead>
		<tbody>
			<?php
			
			try
			{
				$sth = $dbh->prepare(	"SELECT * " .
										"FROM CourseCLOInformation " .
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
				$num = $row->CLONumber;
				print '<tr>';
				print '<td><input type="checkbox" name="remove[' . $row->CLOID .
					']" /></td>';
				print '<td>' . $num . '</td>';
				print '<td>' . $row->Description . '</td>';
				print '<td>' . $row->Outcomes . '</td>';
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" />
</form>

<h2>Reorder CLOs</h2>
<?php

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 3))
{
	print '<p class="error">Nothing to reorder.</p>';
}

?>
<form action="reorder.php?courseID=<?php echo $courseID; ?>" method="POST">
	<table id="edit">
		<thead>
			<tr>
				<th>Number</th>
				<th>Description</th>
				<th>Outcomes</th>
			</tr>
		</thead>
		<tbody>
			<?php
			
			try
			{
				$sth = $dbh->prepare(	"SELECT * FROM CourseCLOInformation " .
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
				$num = $row->CLONumber;
				print '<tr id="' . $num . '">';
				
				print '<td><span>' . $num . '</span>' .
					'<input type="hidden" name="number[' . $row->CLOID . ']" ' .
					'value="' . $num . '" />' .
					'<button type="button" onclick="moveDown(' . $num .	')">' .
					'+</button>' .
					'<button type="button" onclick="moveUp(' . $num . ')">' .
					'-</button></td>';
				
				print '<td>' . $row->Description . '</td>';
				print '<td>' . $row->Outcomes . '</td>';
				print '</tr>';
			}
			
			?>
		</tbody>
	</table>
	<input type="submit" />
</form>

<h2>Add a New CLO</h2>
<?php

if (isset($_REQUEST['error']) AND ($_REQUEST['error'] == 1))
{
	print '<p class="error">You must provide at least one ABET outcome for a ' .
		'CLO.</p>';
}

?>
<p>
	Note: In order to preserve CLO history, it is not possible to edit a CLO. If
	you would like to change the details of a courses CLO, you must create a new
	CLO, and delete the old one.
</p>

<form action="create.php?courseID=<?php echo $courseID; ?>" method="POST">
	<label for="description">Description</label>
	<textarea id="description" name="description" cols="60" rows="10">
	</textarea>
	
	<label for="outcomes">ABET Outcomes</label>
	<input id="outcomes" type="text" name="outcomes" />
	
	<input type="submit" value="Create New CLO" />
</form>

</body>
</html>
