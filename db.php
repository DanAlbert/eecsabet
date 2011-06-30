<?php

function dbConnect()
{
	$hostname = 'engr-db.engr.oregonstate.edu:3307';
	$username = 'eecsabet';
	$password = 'OPtbHauT';
	$database = 'eecsabet';
	
	// 131072: CLIENT_MULTI_RESULTS
	$con = mysql_connect($hostname, $username, $password, TRUE, 131072);
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
