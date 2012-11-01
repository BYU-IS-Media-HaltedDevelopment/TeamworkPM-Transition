<?php

/*
A dashboard task
*/
class DashboardTask
{
	private $externalId;
	private $description;
	private $assigner;	
	private $assignee; 
	private $deadlineDate;

	/*
	Constructor
	*/
	public function __construct($externalId, $description, 
		$assigner, $assignee, $deadlineDate)
	{
		$this->externalId = $externalId; 	
		$this->description = $description;
		$this->assigner = $assigner;
		$this->assignee = $assignee;
		$this->deadlineDate = $deadlineDate;
	}
	
	public function __get($property)
	{
		if(property_exists($this, $property))	
			return $this->$property;
	}

	public function __set($property, $value)
	{
		if(property_exists($this, $property))	
			$this->$property = $value;
	}
}
?>
