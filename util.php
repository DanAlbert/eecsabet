<?php

function cleanIntString($str)
{
	$decimalPos = strpos($str, '.');
	
	if ($decimalPos !== false)
	{
		$str = preg_replace('/\D/', '', substr($str, 0, $decimalPos));
	}
	
	return preg_replace('/\D/', '', $str);
}

?>
