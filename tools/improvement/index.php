<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
	
	<script type="text/javascript"
		src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
	</script>
	
	<script type="text/javascript">
	function getOutcomes()
	{
		var dept = $("select#dept").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-outcomes.php',
			data: { dept: dept },
			dataType: 'html',
			beforeSend: function()
			{
				$("span#status").html('Loading program outcomes...');
			},
			success: function(result)
			{
				$("select#outcome").html(result);
				getImprovement();
				$("span#status").html('');
			},
			error: function(xhr)
			{
				$("span#status").html('An error occured while loading ' +
					'program outcomes: ' + xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	function getImprovement()
	{
		var dept = $("select#dept").val();
		var outcome = $("select#outcome").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-improvement.php',
			data: { dept: dept, outcome: outcome },
			dataType: 'html',
			beforeSend: function()
			{
				$("textarea#improvement").html(
					'Loading program outcome infomation...');
			},
			success: function(result)
			{
				$("textarea#improvement").html(result);
				$("div#preview").html(result);
			},
			error: function(xhr)
			{
				$("textarea#improvement").html('An error occured while loading ' +
					'program outcome info: '
					+ xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	$(document).ready(function()
	{
		getOutcomes();
		
		$("select#dept").change(function()
		{
			getOutcomes();
		});
		
		$("select#outcome").change(function()
		{
			getImprovement();
		});
	});
	</script>
</head>
<body>
<a href="../index.php">Return to Adminstration Page</a> |
<a href="readme.html">Help</a><br />
<br />
<form action="submit.php" method="POST">
	<select id="dept" name="dept">
	<?php
	
	include_once '../../debug.php';
	require_once '../../db.php';

	$dbh = dbConnect();
	
	try
	{
		$sth = $dbh->prepare(
			"SELECT DISTINCT Dept " .
			"FROM Outcomes " .
			"ORDER BY Dept ASC");
		
		$sth->execute();
	}
	catch (PDOException $e)
	{
		die('PDOException: ' . $e->getMessage());
	}
	
	while ($row = $sth->fetch())
	{
		print '<option value="' . $row->Dept . '">' . $row->Dept . '</option>';
	}
	
	?>
	</select>
	
	<select id="outcome" name="outcome">
	</select>
	
	<label for="improvement">Suggested Curriculum Improvement. You may use HTML
		here</label>
	
	<textarea rows="20" cols="100" id="improvement" name="improvement">
	</textarea>
	
	<input type="submit" value="Submit" />
</form>

<h2>Preview</h2>
<div id="preview" style="border: 2px solid #c3c3c3; padding: 5px;">
</div>

</body>
</html>
