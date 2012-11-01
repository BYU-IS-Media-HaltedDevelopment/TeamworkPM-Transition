<?php

require_once "TeamworkPortal.php";

/**
 * Manages all of the people in teamwork.  This is a
 * singleton.
 */
class TeamworkPersonManager
{
    private static $instance = null;
    
    /**
     * Index of of persons by their e-mail address
     */
    private static $emailPersonIndex = array();
    
    /**
     * Private constructor
     */
    private function __construct()
    {
	$this->loadFromNet();
    }
    
    public static function getInstance()
    {
	if(self::$instance == null)
	    self::$instance = new TeamworkPersonManager();
	
	return self::$instance;
    }
    
    /**
     * Checks for a person with a given e-mail address
     * @param type $email The email address to be searched for
     * @return True if the person was found and false otherwise
     */
    public function containsPersonWithEmail($email)
    {
	return array_key_exists($email, $this->emailPersonIndex);
    }
    
    /**
     * Loads the people from the Teamwork website
     */
    private function loadFromNet()
    {
	$teamworkResponse = TeamworkPortal::getQuery("people.json");
	if(!$teamworkResponse)
	{
	    echo "Couldn't get the people";
	    die();
	}
	
	$personArray = json_decode($teamworkResponse);
	
	for($i = 0; $i < count($personArray->people); $i++)
	{
	    //var_dump($personArray->people[$i]->{'user-name'});
	    $this->emailPersonIndex[$personArray->people[$i]->{'email-address'}] 
						    = $personArray->people[$i];
	}
    }
}

?>
