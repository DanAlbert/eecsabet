<?php

include_once 'debug.php';
require_once 'assessment.php';
require_once 'comments.php';
require_once 'db.php';
require_once 'util.php';

$dbh = dbConnect();

$courseInstanceID = $_REQUEST['courseInstanceID'];
$method = $_POST['method'];
$satisfactory = $_POST['satisfactory'];
$recs = $_POST['recs'];

if (sizeof($method) == 0)
{
	onError($courseInstanceID, 1);
	return;
}

try
{
	$dbh->beginTransaction();
}
catch (PDOException $e)
{
	die('PDOException: ' . $e->getMessage());
}

# Turn the nightmare of POST data into something usable
$clos = array();
for ($i = 0; $i < sizeof($method); $i++)
{
	$cloID = key($method);
	
	if (sizeof($method[$cloID]) == 0)
	{
		onError($courseInstanceID, 1);
		return;
	}
	
	$set = new AssessmentSet($cloID);
	
	for ($j = 0; $j < sizeof($method[$cloID]); $j++)
	{
		$cleanedMethod = current($method[$cloID]);
		$cleanedSatisfactory = cleanIntString(current($satisfactory[$cloID]));
		
		$set->add(new Assessment($cleanedMethod, $cleanedSatisfactory));
		
		next($method[$cloID]);
		next($satisfactory[$cloID]);
	}
	
	$clos[] = $set;
	
	next($method);
}

# Remove existing metrics
try
{
	$sth = $dbh->prepare(
		"DELETE FROM CLOAssessment WHERE CourseInstanceID=:id");
	
	$sth->bindParam(':id', $courseInstanceID);
	$sth->execute();
}
catch (PDOException $e)
{
	$dbh->rollback();
	onError($courseInstanceID, 2);
	return;
}

foreach ($clos as $clo)
{
	foreach ($clo->getSet() as $a)
	{
		if (($a->getMethod() == '') or ($a->getMethod() == null))
		{
			$dbh->rollback();
			return onError($courseInstanceID, 1);
		}

		if (($a->getSatisfactory() == '') or ($a->getSatisfactory() == null))
		{
			$dbh->rollback();
			return onError($courseInstanceID, 1);
		}
		
		# Submit new metrics
		try
		{
			$sth = $dbh->prepare(
				"INSERT INTO CLOAssessment " .
				"(CourseInstanceID, CLOID, Method, Satisfactory) " .
				"VALUES (:id, :cloid, :method, :satisfactory)");
			
			$sth->bindParam(':method', $a->getMethod());
			$sth->bindParam(':satisfactory', $a->getSatisfactory());
			$sth->bindParam(':cloid', $clo->getCLOID());
			$sth->bindParam(':id', $courseInstanceID);
			
			$sth->execute();
		}
		catch (PDOException $e)
		{
			$dbh->rollback();
			onError($courseInstanceID, 2);
			return;
		}
	}
}

# Don't lost assessment changes if the comments fail
$dbh->commit();

# Try to submit comments
$result = submitInitialComments($dbh, $courseInstanceID, $recs);

# If successful, update course state
if ($result == 0)
{
	try
	{
		$sth = $dbh->prepare(
			"UPDATE CourseInstance " .
			"SET State='Approved' " .
			"WHERE ID=:id");
		
		$sth->bindParam(':id', $courseInstanceID);
		$sth->execute();
	}
	catch (PDOException $e)
	{
		$dbh->rollback();
		onError($courseInstanceID, 5);
		return;
	}
}

# Return result
onError($courseInstanceID, $result);

function onError($courseInstanceID, $errno)
{
	header('Location: index.php?courseInstanceID=' . $courseInstanceID .
		'&error=' . $errno);
	return;
}

?>
