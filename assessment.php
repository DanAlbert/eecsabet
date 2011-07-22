<?php

class Assessment
{
	private $method;
	private $mean;
	private $median;
	private $high;
	private $satisfactory;
	private $attained;
	
	public function getMethod()
	{
		return $this->method;
	}
	
	public function setMethod($value)
	{
		$this->method = $value;
	}
	
	public function getMean()
	{
		return $this->mean;
	}
	
	public function setMean($value)
	{
		$this->mean = $value;
	}
	
	public function getMedian()
	{
		return $this->median;
	}
	
	public function setMedian($value)
	{
		$this->median = $value;
	}
	
	public function getHigh()
	{
		return $this->high;
	}
	
	public function setHigh($value)
	{
		$this->high = $value;
	}
	
	public function getSatisfactory()
	{
		return $this->satisfactory;
	}
	
	public function setSatisfactory($value)
	{
		$this->satisfactory = $value;
	}
	
	public function getAttained()
	{
		return $this->attained;
	}
	
	public function setAttained($value)
	{
		$this->attained = $value;
	}
	
	public function __construct(
		$method,
		$satisfactory,
		$mean = null,
		$median = null,
		$high = null,
		$attained = null)
	{
		$this->method = $method;
		$this->mean = $mean;
		$this->median = $median;
		$this->high = $high;
		$this->satisfactory = $satisfactory;
		$this->attained = $attained;
	}
}

class AssessmentSet
{
	private $assessments;
	private $cloid;
	
	public function add(Assessment $assessment)
	{
		$this->assessments[] = $assessment;
	}
	
	public function getSet()
	{
		return $this->assessments;
	}
	
	public function clear()
	{
		$this->assessments = array();
	}
	
	public function getCLOID()
	{
		return $this->cloid;
	}
	
	public function setCLOID($value)
	{
		$this->cloid = $value;
	}
	
	public function __construct($cloid)
	{
		$this->assessments = array();
		$this->cloid = $cloid;
	}
}

?>
