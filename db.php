<?php

function dbConnect()
{
	$driver = 'mysql';
	$hostname = 'engr-db.engr.oregonstate.edu';
	$username = 'eecsabet';
	$password = 'OPtbHauT';
	$database = 'eecsabet';
	$port = 3307;
	
	try
	{
		$dbh = new PDO(
			"$driver:host=$hostname;port=$port;dbname=$database",
			$username,
			$password,
			array(	PDO::ATTR_PERSISTENT => true,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
	catch (PDOException $e)
	{
		die('Unable to connect to database (' . $e->getCode() . '): ' .
			$e->getMessage());
	}
	
	$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	
	return $dbh;
}

?>
