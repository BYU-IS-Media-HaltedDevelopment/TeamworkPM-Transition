<?php

require_once ".password";
require_once "TeamworkProject.php";

/**
 * Manages all of the projects in teamwork.  This is a
 * singleton.
 */
class TeamworkProjectManager
{
    private static $instance = null;
    
    /**
     * All projects
     */
    private $projects = array();
    
    /**
     * Index of projects by name
     */
    private $nameProjectIndex = array();
    
    /**
     * Private constructor
     */
    private function __construct()
    {
	$this->loadFromNet();
	return $this;
    }
    
    public static function getInstance()
    {
	if(self::$instance == null) {
	    self::$instance = new self();
	}
	return self::$instance;
    }
    
    /**
     * Gets the projet by the name
     * @param $projName The name of the project to looked for
     * @return The project objet if the project was found and null
     * otherwise.
     */
    public function getProjectByName($projName)
    {
	if(array_key_exists($projName, $this->nameProjectIndex))
	    return $this->nameProjectIndex[$projName];
	else
	    return null;
    }
    
    /**
     * Gets all of the projects
     */
    public function getProjects()
    {
	return $this->projects;
    }
    
    /**
     * Loads the data from the Teamwork website
     */
    private function loadFromNet()
    {
	$teamworkResponse = TeamworkPortal::getData("projects.json");
	if(!$teamworkResponse)
	{
	    echo "Couldn't get the people";
	    die();
	}
	
	$projectArray = ($teamworkResponse)?$teamworkResponse:array();
	
	for($i = 0; $i < count($projectArray->projects); $i++)
	{
	    $newTeamProj = new TeamworkProject($projectArray->projects[$i]->id,
			    $projectArray->projects[$i]->name);
	    $this->nameProjectIndex[preg_replace("/\s/", "", $newTeamProj->name)] 
		    = $newTeamProj; 
	    $this->projects[] = $newTeamProj;
	}	
    }
}

?>
