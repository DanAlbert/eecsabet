<?php

function dbConnect()
{
	$hostname = 'mysql.gingerhq.net';
	$username = 'eecsabet';
	$password = 'hP5fRjZbZ6KcL7MU';
	$database = 'eecsabet';
	
	/*$hostname = 'engr-db.engr.oregonstate.edu:3307';
	$username = 'eecsabet';
	$password = 'OPtbHauT';
	$database = 'eecsabet';*/

	$con = mysql_connect($hostname, $username, $password);
	if (!$con)
	{
		mysql_close($con);
		return null;
	}

	if (!mysql_select_db($database))
	{
		mysql_close($con);
		return null;
	}
	
	return $con;
}

?>
