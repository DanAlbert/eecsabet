<?php

include_once '../../debug.php';
require_once 'latex.php';

header('Location: ' . generateABETSyllabus($_REQUEST['courseID']));

?>
