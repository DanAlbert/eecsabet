<!DOCTYPE html>
<html>
<head>
	<title>Create a new Instructor</title>
	<link rel="stylesheet" type="text/css" href="../../style.css" />
</head>
<body>

<a href="../index.php">Return to Adminstration Page</a> |
<a href="readme.html">Help</a>
<form action="create.php" method="POST">
	<label for="firstName">First Name</label>
	<input id="firstName" type="text" name="firstName" />
	
	<label for="lastName">Last Name</label>
	<input id="lastName" type="text" name="lastName" />
	
	<label for="email">Email</label>
	<input id="email" type="text" name="email" />
	<input type="submit" value="Create New Instructor" />
</form>

</body>
</html>
