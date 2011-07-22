<!DOCTYPE html>
<html>
<head>
	<title>EECS ABET</title>
	
	<link rel="stylesheet" type="text/css" href="../../style.css" />
	
	<script type="text/javascript"
		src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
	</script>
	
	<script type="text/javascript">
	
	function getCourseOfferings()
	{
		$.ajax({
			type: 'GET',
			url: 'get-offerings.php',
			dataType: 'html',
			beforeSend: function()
			{
				$("span#status").html('Loading program outcomes...');
			},
			success: function(result)
			{
				$("select#offering").html(result);
				$("span#status").html('');
				getTerms();
			},
			error: function(xhr)
			{
				$("span#status").html('An error occured while loading ' +
					'program outcomes: ' + xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	function getTerms()
	{
		var id = $("select#offering").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-terms.php',
			data: { id: id },
			dataType: 'html',
			beforeSend: function()
			{
				$("span#status").html('Finding terms...');
			},
			success: function(result)
			{
				$("select#term").html(result);
				$("span#status").html('');
				getInstructors();
			},
			error: function(xhr)
			{
				$("span#status").html('An error occured while loading ' +
					'terms: ' + xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	function getInstructors()
	{
		var id = $("select#offering").val();
		var term = $("select#term").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-instructors.php',
			data: { id: id , term: term},
			dataType: 'html',
			beforeSend: function()
			{
				$("span#status").html('Finding instructors...');
			},
			success: function(result)
			{
				$("select#instructor").html(result);
				$("span#status").html('');
				getAssessmentInfo();
			},
			error: function(xhr)
			{
				$("span#status").html('An error occured while loading ' +
					'terms: ' + xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	function getAssessmentInfo()
	{
		var id = $("select#offering").val();
		var term = $("select#term").val();
		var email = $("select#instructor").val();
		
		$.ajax({
			type: 'POST',
			url: 'get-info.php',
			data: { id: id, term: term, email: email },
			dataType: 'html',
			beforeSend: function()
			{
				$("div#assessment-info").html(
					'Loading program assessment infomation...');
			},
			success: function(result)
			{
				$("div#assessment-info").html(result);
				
				$("span#status").html('');
			},
			error: function(xhr)
			{
				$("div#assessment-info").html('An error occured while loading ' +
					'course assessment info: '
					+ xhr.status + ' ' + xhr.statusText);
			}});
	}
	
	$(document).ready(function()
	{
		getCourseOfferings();
		
		$("select#term").change(function()
		{
			getTerms();
		});
		
		$("select#offering").change(function()
		{
			getAssessmentInfo();
		});
	});
	</script>
</head>

<body>
<a href="../index.php">Return to Adminstration Page</a> |
<a href="readme.html">Help</a><br />
<br />
<form action="javascript:void()" >
	<select id="offering" name="offering">
	</select>
	
	<select id="term" name="term">
	</select>
	
	<select id="instructor" name="instructor">
	</select>
	
	<span id="status"></span>
	
	<div id="assessment-info">
	</div>
</form>
</body>

</html>
