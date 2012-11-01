<?php

/*
Represents a task that needs to be performed in order to migrate the
dashboard task.
*/
class MigrationTask 
{
    
	private $todoMessage;	// the message of what needs to be performed to get this done
	private $dashTask;  
	private $teamTodoItem;
	private $jsonTaskId; 
        
        function __construct($todoMessage, $dashTask, $teamTodoItem) 
        {
            $this->todoMessage = $todoMessage;
	    $this->dashTask = $dashTask;
	    $this->teamTodoItem = $teamTodoItem;
	    $this->jsonTaskId = "generic";
        }
	
    /**
     * Getter
     */
    public function __get($property)
    {
	    if(property_exists($this, $property))	
		    return $this->$property;
    }

    /**
     * Setter
     */
    public function __set($property, $value)
    {
	    if(property_exists($this, $property))	
		    $this->$property = $value;
    }  	
    
    /*
     * Returns the json representation of this task 
     */
    public function exportToArray()
    {
	$taskArray = array();
	$taskArray["taskId"] = $this->jsonTaskId;
	$taskArray["description"] = $this->dashTask->description;
	$taskArray["assigner"] = $this->dashTask->assigner->name;
	$taskArray["assignee"] = $this->dashTask->assignee->name;
	$taskArray["deadlineDate"] = $this->dashTask->deadlineDate;
	return $taskArray;
    }
	
    /**
     * Returns the html string for this task
     */
    function toHtml() 
    {
	return "<p>" . $this->todoMessage . "</p>";
    }
}

/**
 * Represents a migration task that is done.
 */
class CompletedTask extends MigrationTask
{
    function __construct($todoMessage, $dashTask, $teamTodoItem) 
    {
	parent::__construct($todoMessage, $dashTask, $teamTodoItem);
	$this->jsonTaskId = "completed-task";
    }
    
    function toHtml()
    {
	$html = "<p>Completed Task:</p>";
	$html .= "<p>Dashboard had a task described as</p>";
	$html .= $this->dashTask->description;
	$html .= "<p>Teamwork had a teamwork todo item of.</p>";
	$html .= $this->teamTodoItem->content;
	return $html;
    }  
}

class UnmatchableDashTask extends MigrationTask
{
    function __construct($whyMessage, $dashTask) 
    {
	parent::__construct($whyMessage, $dashTask, null);
	$this->jsonTaskId = "unmatchable-task";
    }
    
    function toHtml()
    {
	$html = "<p>" . $this->todoMessage . "</p>";
	return $html;
    }
}

class NeedsMigrationTask extends MigrationTask
{
    public function __construct($todoMessage, $dashTask, $teamTodoItem) 
    {
	parent::__construct($todoMessage, $dashTask, $teamTodoItem);
	$this->jsonTaskId = "to-migrate";
    }
    
    public function toHtml()
    {
	$html = "<p>Dashboard Task needs to be migrated: </p>";
	$html .= $this->dashTask->description;
	return $html;
    }
}

/*class MissingPersonTask extends MigrationTask
{
    
}*/
?>
