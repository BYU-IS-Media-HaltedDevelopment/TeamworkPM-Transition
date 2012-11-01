<?php
/**
 * Manages all of the tasks from Dashboard for a particular 
 * user.
 */
class DashboardTaskManager
{   
    private $dashTasks;
    
    public function __construct($dashUsername)
    {
	$this->dashTasks = DashboardPortal::getDashboardTasksByUserId(
		DashboardPortal::getUserIdByUsername($dashUsername));
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
