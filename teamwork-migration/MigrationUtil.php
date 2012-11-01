<?php

require_once(".password");
require_once 'DashboardTask.php';
require_once 'MigrationFactory.php';
require_once "TeamworkProject.php";
require_once "TeamworkPortal.php";
require_once "TeamworkPersonManager.php";
require_once "TeamworkProjectManager.php";
require_once "DashboardTaskManager.php";
require_once "DashboardPortal.php";

/*
Represents the main utility for migrating tasks
*/
class MigrationUtil
{	
	private $teamPersManager;
	private $teamProjManager;
	private $dashTaskManager; 
	
	/*
	Constructor
	*/
	public function __construct($username)
	{
	    $this->teamPersManager = TeamworkPersonManager::getInstance();
	    $this->teamProjManager = TeamworkProjectManager::getInstance();
	    $this->dashTaskManager = new DashboardTaskManager($username);
	}

	/*
	Gets an array of migration tasks that need to be performed
	*/
	public function getMigrationTasks($dashUsername, $apiKey)
	{
	    $migrationTasks = array();
	    foreach($this->dashTaskManager->dashTasks as $dashTask)
		$migrationTasks[] = $this->createMigrationTask($dashTask);
	    
	    //echo "Number of migration tasks: " . count($migrationTasks);
	    $tasksJson = array();
	    foreach($migrationTasks as $migrationTask)
	    {
		$tasksJson[] = $migrationTask->exportToArray();
		/*echo "<hr />";
		echo $migrationTask->toHtml();		
		echo "<hr />";
		print_r($migrationTask->toJson());*/
		
	    }
	    
	    return json_encode($tasksJson);
	}
	
	/**
	 * Creates a migration task for the given dashboard task
	 * @param type $dashTask The dashboard task to match
	 */
	public function createMigrationTask($dashTask)
	{
	    // is the task matchable?
	    
	    
	    $matchingTeamProj = $this->teamProjManager->getProjectByName($dashTask->externalId);
	    
	    // if the task didn't match any project
	    if($matchingTeamProj ==  null)
	    {
		return new UnmatchableDashTask("No teamwork project matches the external id of: " . 
			$dashTask->externalId
			, $dashTask);
	    }
	    
	    // get the matching todo item
	    if(!$matchingTeamProj->todoListLoaded())
		$matchingTeamProj->loadTodoList();
	    
	    $matchingTodoItem = $matchingTeamProj->
		    getTodoItemBySimilarDescrip($dashTask->description);
	    
	    // if a matching todo item was found, then it doesn't need to be migrated
	    if($matchingTodoItem != null)
		return new CompletedTask("Completed task",
		    $dashTask, $matchingTodoItem);
	    
	    // does someone need to be imported?
	    $NEED_ASSIGNER = 
		$this->teamPersManager->containsPersonWithEmail($dashTask->assigner->email);
	    $NEED_ASSIGNEE = 
		$this->teamPersManager->containsPersonWithEmail($dashTask->assignee->email);
	    
	    // if it didn't match, then it needs to be migrated
	    return new NeedsMigrationTask("Needs to be migrated", $dashTask, null);
	}
	
	/**
	 * Indicates whether or no the task is matcheable.  
	 * @return bool Returns true if the task is matcheable 
	 * and false otherwise.
	 */
	private function taskMatchable()
	{
	    
	}
}

$migrationUtil = new MigrationUtil($_GET["username"]);
$jsonTasks = $migrationUtil->getMigrationTasks("sg99", "cut527march");
print_r($jsonTasks);

?>
