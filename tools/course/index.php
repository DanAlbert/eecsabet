<!DOCTYPE html>
<html>
<head>
	<title>Create a new Course</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>

<a href="../index.php">Return to Adminstration Page</a>
<form action="create.php" method="POST">
	<label for="dept">Department</label>
	<input id="dept" type="text" name="dept" />
	
	<label for="courseNumber">Course Number</label>
	<input id="courseNumber" type="text" name="courseNumber" />
	
	<label for="creditHours">Credit Hours</label>
	<input id="creditHours" type="text" name="creditHours" />
	
	<label for="description">Description</label>
	<textarea id="description" name="description" cols="60" rows="10"></textarea>
	
	<input type="submit" value="Create New Course" />
</form>

</body>
</html>
