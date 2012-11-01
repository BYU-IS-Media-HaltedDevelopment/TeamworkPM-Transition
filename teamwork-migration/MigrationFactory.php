<?php

require_once "MigrationTask.php";

/*
Given a dahsboard task and a list of teamwork projects,
the MigrationTaskFactory houses the matching algorithm and
returns a MigrationTask
*/
class MigrationTaskFactory
{
	private $dashTasks;                     // the dashboard tasks
	private $teamworkProjs;                 // the list of teamwork projects
	private $migrationTasks = array();	// the migration tasks

	/*
	Runs the matching algorithm and returns the list
	of migration tasks
	*/
	public function getMigrationTasks($dashTasks, $teamworkProjs)
	{
		$this->dashTasks = $dashTasks;	
		$this->teamworkProjs = $teamworkProjs;

		//var_dump($this->dashTasks);

		foreach($this->dashTasks as $dashTask)
		{
		    // if the description is empty we can't match anything
		    if($dashTask->description == null)
		    {
			$this->migrationTasks[] = 
				new MigrationTask("Dashboard task doesn't have process description.", 
					$dashTask, null);
			continue;
		    }

		    $matchingTeamworkProjs = $this->getTeamProjsForTask($dashTask);
		    if(count($matchingTeamworkProjs) == 0)
		    {
			$this->migrationTasks[] = 
				new MigrationTask("No matching project in teamwork", 
					$dashTask, null);
			continue;
		    }

		    $matchingProj = $matchingTeamworkProjs[0];
		    if(!$matchingProj->todoListLoaded())
			$matchingProj->loadTodoList(); 

		    $matchingTeamTodoItem = 
			$matchingProj->getTodoItemBySimilarDescrip($dashTask->description);
		    if($matchingTeamTodoItem !=  null)
		    {
			$completedTask = 
			    new CompletedTask("Task already migrated", 
				    $dashTask, $matchingTeamTodoItem);
			
			echo $completedTask->toHtml();
			echo "Found completed task!";
		    }
		}
		
		foreach($this->migrationTasks as $migrationTask)
		{
		    echo "Print tasks!";
		    echo $migrationTask->toHtml();
		}
	}

	/*
	Returns the projects that match Dashboard's task name
	*/
	private function getTeamProjsForTask($dashTask)
	{
		$matches = array();
                for($i = 0; $i < count($this->teamworkProjs); $i++)
                {			
                        if($dashTask->externalId == 
                                        $this->teamworkProjs[$i]->name)
                        {
                                $matches[] = $this->teamworkProjs[$i];
                        }
                }

		return $matches;
	}
}

?>
