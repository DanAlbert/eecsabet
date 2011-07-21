<!DOCTYPE html>
<html>
<head>
	<title>Create a new Course</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>

<a href="../index.php">Return to Adminstration Page</a> |
<a href="readme.html">Help</a>
<form action="create.php" method="POST">
	<label for="dept">Department (Ex. ECE, CS, ENGR, etc.)</label>
	<input id="dept" type="text" name="dept" />
	
	<label for="courseNumber">Course Number</label>
	<input id="courseNumber" type="text" name="courseNumber" />
	
	<label for="courseTitle">Course Title</label>
	<input id="courseTitle" type="text" name="courseTitle" />
	
	<label for="creditHours">Credit Hours</label>
	<input id="creditHours" type="text" name="creditHours" />
	
	<label for="description">Description</label>
	<textarea id="description" name="description" cols="60" rows="10"></textarea>
	
	<label for="structure">Strucure</label>
	<textarea id="structure" name="structure" cols="60" rows="10"></textarea>
	
	<input type="submit" value="Create New Course" />
</form>

</body>
</html>
