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
				getOutcomeInfo();
				$("span#status").html('');
			},
			error: function(xhr)
			{
				$("span#status").html('An error occured while loading ' +
					'program outcomes: ' + xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	function getOutcomeInfo()
	{
		var dept = $("select#dept").val();
		var outcome = $("select#outcome").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-info.php',
			data: { dept: dept, outcome: outcome },
			dataType: 'html',
			beforeSend: function()
			{
				$("div#outcome-info").html(
					'Loading program outcome infomation...');
			},
			success: function(result)
			{
				$("div#outcome-info").html(result);
				
				$("span#status").html('');
			},
			error: function(xhr)
			{
				$("div#outcome-info").html('An error occured while loading ' +
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
			getOutcomeInfo();
		});
	});
	</script>
</head>

<body>
<a href="../index.php">Return to Adminstration Page</a>
<form action="javascript:void()" >
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
	
	<span id="status"></span>
	
	<div id="outcome-info">
	</div>
</form>
</body>

</html>
