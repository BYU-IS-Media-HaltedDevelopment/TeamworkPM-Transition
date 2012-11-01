<?php

/**
 * Represents a person in Dashboard
 */
class DashPerson 
{
    private $name;
    private $email;
    
    /**
     * Constructor
     * @param string $name Name of the person
     * @param string $email Email of the person
     */
    public function __construct($name, $email)
    {
	$this->name = $name;
	$this->email = $email;
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
