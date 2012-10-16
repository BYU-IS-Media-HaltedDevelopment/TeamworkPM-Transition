<?php
require_once(".password");

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/*
 * Utility function for replacing all underscores with hyphens in 
 * json variable names
 */
function hyphenToUnderscore($jsonString)
{
    return str_replace("-", "_", $jsonString);
}

/*
Represents the main utility for migrating tasks
*/
class MigrationUtil
{
	private $dashTasks = array();
	private $teamworkProjects = array();
	private $dashboardId;

	// connection the queue dashboard database
	private $qDb;

	/*
	Constructor
	*/
	public function __construct()
	{
		global $api_keys;

		$this->qDb = new mysqli($api_keys["luke's"]['queue']['host'], 
					$api_keys["luke's"]['queue']['login'], 
					$api_keys["luke's"]['queue']['pass'], 
					$api_keys["luke's"]['queue']['db']);

		if($this->qDb->connect_errno) 
		{
			echo "Died on connection";
			die();
		}
	}

	/*
	Deconstructor
	*/
	public function __destruct()
	{
		$this->qDb->close();
	}

	/*
	Gets an array of migration tasks that need to be performed
	*/
	public function getMigrationTasks($dashUsername, $apiKey)
	{
		//$this->getDashboardId("mjwright");
		//$this->getDashboardId("swg5");
		$this->getDashboardId("sg99");
		$this->loadDashTasks();
		$this->loadTeamworkProjects();

		$migrationFactory = new MigrationTaskFactory();
		$migrationFactory->getMigrationTasks($this->dashTasks, $this->teamworkProjects);
	}

	/*
	Loads the dahsboard tasks into the array of dashboard tasks

	@param dashUsername The dashboard username of the the user
	*/
	private function loadDashTasks()
	{
		$dashTasksQuery = "
			SELECT
				sel.*,
				p.process_name,
				t.description AS process_task_description
			FROM
				(SELECT
					t.my_task_id,
					t.task_id,
					t.entry_date,
					t.deadline_date,
					t.description,
					t.priority,
					apers.first_name AS assigner_first_name,
					apers.last_name AS assigner_last_name,
					bpers.first_name AS assignee_first_name,
					bpers.last_name AS assignee_last_name,
					c.external_id,
					ci.scope,
					ci.course_title,
					ci.description AS course_description,
					t.processes_in_production_id,
					cpers.first_name AS designer_first_name,
					cpers.last_name AS designer_last_name
				FROM
					my_tasks AS t,
					task_assignments AS ta,
					person AS apers,
					person AS bpers,
					person AS cpers,
					course AS c,
					course_info AS ci
				WHERE
					ta.my_task_id = t.my_task_id AND
					t.course_id = c.course_id AND
					c.course_id = ci.course_id AND
					apers.person_id = ta.assigner_id AND
					bpers.person_id = ta.assignee_id AND
					cpers.person_id = ci.portfolio_designer_id AND
					(ta.assignee_id IN (".$this->dashboardId.
						") OR ta.assigner_id IN (".$this->dashboardId.")) AND
					t.closed_date IS NULL AND
					ta.marked_complete = 'N'
					LIMIT 500
				) AS sel
			LEFT JOIN processes_in_production AS pip ON sel.processes_in_production_id = pip.id
			LEFT JOIN processes AS p ON pip.process_id = p.process_id
			LEFT JOIN tasks AS t ON pip.process_id = t.process_id AND t.curr_task = sel.task_id
			LIMIT 500";

		// Run the query 
		$queryResult = $this->qDb->query($dashTasksQuery);

		// if we didn't get anything valid back, exit
		if(!$queryResult)
		{
			//var_dump($queryResult);
			echo $this->qDb->error;
			die();
		}

		while($taskRecord = $queryResult->fetch_array())
		{
			$this->dashTasks[] = new DashboardTask($taskRecord["external_id"],
				$taskRecord["description"]);   
		}


		$queryResult->close();
	}

	/*
	Loads the dahsboard projects into our array of projects
	*/
	private function loadTeamworkProjects()
	{
		echo "Getting projects";	
		global $api_keys;
		$credentials = $api_keys["luke's"]["teamwork"]."xxx";
		$ch = curl_init("http://byuis.teamworkpm.net/" . "projects.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
						"Accept: application/xml",
						"Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, "cut527march:xxx");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$projectJson = curl_exec($ch);	

		$teamworkResponse = json_decode($projectJson);
		//$this->teamworkProjects = $teamworkResponse->projects;
		for($i = 0; $i < count($teamworkResponse->projects); $i++)
		{
			$this->teamworkProjects[$i] = 
				new TeamworkProject($teamworkResponse->projects[$i]->id, 
							$teamworkResponse->projects[$i]->name);
		}
	}

	/*
	Gets the user's dahsboard id
	*/
	private function getDashboardId($dashUsername)
	{
		$userIdQuery = " 
			SELECT  
				person_id 
			FROM 
				person 
			WHERE 
				login_name ='" . $dashUsername . "'";

		// Run the query 
		$queryResult = $this->qDb->query($userIdQuery);

		// if we didn't get anything valid back, exit
		if(!$queryResult)
		{
			echo $this->qDb->error;
			die();
		}
		else
		{
			$idRecord = $queryResult->fetch_array();
			$this->dashboardId = $idRecord["person_id"];
		}

		$queryResult->close();
	}
}

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
				new MigrationTask("Dashboard task doesn't have process description.");
			continue;
		    }

		    $matchingTeamworkProjs = $this->getTeamProjsForTask($dashTask);
		    if(count($matchingTeamworkProjs) == 0)
		    {
			$this->migrationTasks[] = 
				new MigrationTask("No matching project in teamwork");
			continue;
		    }

		    $matchingProj = $matchingTeamworkProjs[0];
		    if(!$matchingProj->todoListLoaded())
			$matchingProj->loadTodoList(); 

		    if($matchingProj->getTodoItemBySimilarDescrip($dashTask->description) 
			    !=  null)
		    {
			$completedTask = new CompletedTask("Task already migrated");
			//$this->migrationTasks[] = new CompletedTask("Task already migrated");
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


/*
Represents a task that needs to be performed in order to migrate the
dashboard task.
*/
class MigrationTask 
{
	private $todoMessage;	// the message of what needs to be performed to get this done
	private $dashTask;  
	private $teamTodoItem;
        
        function __construct($todoMessage) 
        {
            $this->todoMessage = $todoMessage;
        }
	
    /**
     * Returns the html string for this task
     */
    function toHtml() 
    {
	return "";
    }
}

/**
 * Represents a migration taks that is done.
 */
class CompletedTask extends MigrationTask
{
    function toHtml()
    {
	return "<p>This task is done</p>";
    }
}

/*class UnmatchableDashTask extends MigrationTaks
{
    
}*/

/*class MissingPersonTask extends MigrationTask
{
    
}*/

/*
A dashboard task
*/
class DashboardTask
{
	private $externalId;
	private $processName;
	private $description;

	/*
	Constructor
	*/
	public function __construct($externalId, $description)
	{
		$this->externalId = $externalId; 	
		$this->description = $description;
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

/*
Represents a TeamworkProject
*/
class TeamworkProject
{
	private $id;		// teamwork id for the project
	private $name;		// name of the project
	private $todoLists; 	// list of itmes to do for this project

	/*
	Constructor
	*/
	public function __construct($id, $projName)
	{
		$this->id = $id;

		// sanitize the name
                $matches = null;
		if(preg_match('/.*-...-.../', $projName, $matches, 0, 0))
                    $this->name = $matches[0];
                else
                    $this->name = $projName;
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
	
	public function getTodoItemBySimilarDescrip($descrip)
	{   
	    if(!$this->hasTodoList())
		return;
	    
	    foreach($this->todoLists as $todoList)
	    {
		$searchResult = $todoList->getTodoItemBySimilarDescrip($descrip);
	    }
	}
	
	private function hasTodoList() 
	{
	    if(!isset($this->todoLists))
		return false;
	    else
		return true;
	}

	/*
	Loads the TodoList from Teamwork
	*/
	public function loadTodoList()
	{
		//echo "Loading todo list! <br />";	
		// echo $this->id . "<br />";

		global $api_keys;
		$credentials = $api_keys["luke's"]["teamwork"]."xxx";
		$ch = curl_init("http://byuis.teamworkpm.net/projects/" . $this->id . "/todo_lists.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
						"Accept: application/xml",
						"Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, "cut527march:xxx");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$todoListJson = hyphenToUnderscore(curl_exec($ch));
		$todoLists = json_decode($todoListJson)->todo_lists;
                for($i = 0; $i < count($todoLists); $i++)
                {
                    $this->todoLists[] = new TeamworkTodoList($todoLists[$i]->project_id);
                    $todoItems = $todoLists[$i]->todo_items;
                    for($j = 0; $j < count($todoItems); $j++)
                        $this->todoLists[$i]->
			    addTodoItem(new TodoItem($todoItems[$j]->description));
                }
	}

	/*
	Indicates whether or not the list is loaded
	*/
	public function todoListLoaded()
	{
		return isset($this->todoList);	
	}
}

/*
Represents a TodoList for a project
*/
class TeamworkTodoList
{
	private $id;
        private $todoItems = array();
        
        /*
         * Constructor
         */
        public function __construct($id)
        {
            $this->id = $id;
        }

        public function addTodoItem($todoItem)
        {
            $this->todoItems[] = $todoItem;
        }

	/**
	 * Gets a a todo item by its description.  It returns
	 * the first one that is found.
	 * @param descrip 
	 */
	public function getTodoItemBySimilarDescrip($desrip)
	{
	    foreach($this->todoItems as $todoItem)
	    {
		if($this->dashDescripSimilarToTeamContent($desrip, $todoItem->content))
		{
		    echo "Match!<br />";
		    	return $todoItem;
		}
	    }
	}
	
	/**
	 * Checks to see if the dashboard description matches the teamwork
	 * description.
	 * @param type $dashDescrip
	 * @param type $teamContent
	 * @return type
	 */
	private function dashDescripSimilarToTeamContent($dashDescrip, $teamContent)
	{   
	    // sanitize the dash-description by removing html and tabs
	    $sanitizedDashDescrip = preg_replace("/<br\/*>/", "", $dashDescrip);
	    $sanitizedDashDescrip = preg_replace("/\\\\t/", "", $sanitizedDashDescrip);
	    
	    // remove the tag that indicates this task was migrated from Dashboard
	    // if there is one
	    //$importedTag = "/This task was originally assigned in Dashboard.*\./";
	    $importedTag = "/This task was originally assigned in Dashboard/";
	    $sanitizedTeamDescrip = preg_replace($importedTag, "", $teamContent);
	    
	    // remove all whitespace and punctuation
	    //$puncAndWhiteSpace = '/[!\\"#\$%&\'()\*\+,-\./:;<=>/?@\[\]\^_`{|}~\s]/';
	    $puncAndWhiteSpace = '/[:,-\.\s]/';
	    $sanitizedDashDescrip = preg_replace($puncAndWhiteSpace, "", $sanitizedDashDescrip);
	    $sanitizedTeamDescrip = preg_replace($puncAndWhiteSpace, "", $teamContent);
	    
	    // make it a case insensitive comparison
	    $sanitizedDashDescrip = strtolower($sanitizedDashDescrip);
	    $sanitizedTeamDescrip = strtolower($sanitizedTeamDescrip);
	    
	    /*echo "Trying to match:";
	    echo "<br />Dashboard description: " . $sanitizedDashDescrip;
	    echo "<br />Team description: " . $sanitizedTeamDescrip;
	    echo "<br /><br />";*/
	    
	    similar_text($sanitizedDashDescrip, $sanitizedTeamDescrip, $percentSimilar);
	    //echo "Percent simililar: " . $percentSimilar . "<br />";
	    
	    // consider it match if they are more than 50 percent similar
	    $PERCENT_THRESHOLD = 50;
	    if($percentSimilar > $PERCENT_THRESHOLD)
		return True;
	    else
		return False;
	}
}

/*
Represents a TodoItem inside a TodoList
*/
class TodoItem
{
    private $content;
    
    function __construct($description)
    {
        $this->content = $description;
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


<html>
<head>
	<script type="text/javascript" src="underscore-min.js"></script>
	<script type="text/javascript" src="backbone-min.js"></script>
	<script type="text/javascript">


	</script>
</head>
<body>
<?php
echo "Hello there!";
$migrationUtil = new MigrationUtil; 
$migrationUtil->getMigrationTasks("asdf", "adf");
?>
</body>
</html>
