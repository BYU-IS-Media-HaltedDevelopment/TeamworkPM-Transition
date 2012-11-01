<?php

require_once 'DashPerson.php';

/**
 * Represents a portal to the Dahsboard project.
 */
class DashboardPortal
{
    public static $dashDb = null;
    
    /**
     * Connects to the dashboard database
     * @global type $api_keys
     */
    private static function connect()
    {
	    global $api_keys;

	    DashboardPortal::$dashDb = new mysqli($api_keys["luke's"]['queue']['host'], 
				    $api_keys["luke's"]['queue']['login'], 
				    $api_keys["luke's"]['queue']['pass'], 
				    $api_keys["luke's"]['queue']['db']);
	    
	    if(DashboardPortal::$dashDb->connect_errno) 
	    {
		    echo "Died on connection" . DashboardPortal::$dashDb->connect_errno;
		    die();
	    }
    }
    
    private static function disconnect()
    {
	DashboardPortal::$dashDb->close();
	DashboardPortal::$dashDb = null;
    }
    
    /**
     * Gets the user's user id by their dashboard username
     * @param type $dashboardUsername The user's dashboard username
     */
    public static function getUserIdByUsername($dashUsername)
    {
	if(DashboardPortal::$dashDb == null)
	    DashboardPortal::connect ();
	
	$userIdQuery = " 
		SELECT  
			person_id 
		FROM 
			person 
		WHERE 
			login_name ='" . $dashUsername . "'";

	// Run the query 
	$queryResult = DashboardPortal::$dashDb->query($userIdQuery);

	// if we didn't get anything valid back, exit
	if(!$queryResult)
	{
		echo DashboardPortal::$dashDb->error;
		die();
	}

	$idRecord = $queryResult->fetch_array();
	
	DashboardPortal::disconnect();
	
	return $idRecord["person_id"];
    }
    
    /**
     * Get the dashboard dasks for a particular user.
     * @param type $userId The dashboard user id
     * @return The dashboard tasks
     */
    public static function getDashboardTasksByUserId($userId)
    {
	if(DashboardPortal::$dashDb == null)
	    DashboardPortal::connect ();

	    $dashTasksQuery = "SELECT
				sel.*,
				p.process_name,
				t.description AS process_task_description,
				apers_ei.work_email as apers_email,
				bpers_ei.work_email as bpers_email
			FROM
				(SELECT
					t.my_task_id,
					t.task_id,
					t.entry_date,
					t.deadline_date,
					t.description,
					t.priority,
					apers.person_id AS assigner_id,
					apers.first_name AS assigner_first_name,
					apers.last_name AS assigner_last_name,
					bpers.person_id AS assignee_id,
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
					(ta.assignee_id IN (".$userId.") OR ta.assigner_id IN (".$userId.")) AND
					t.closed_date IS NULL AND
					ta.marked_complete = 'N'
					LIMIT 500
				) AS sel
			LEFT JOIN processes_in_production AS pip ON sel.processes_in_production_id = pip.id
			LEFT JOIN processes AS p ON pip.process_id = p.process_id
			LEFT JOIN tasks AS t ON pip.process_id = t.process_id AND t.curr_task = sel.task_id
			LEFT JOIN employee_info AS apers_ei ON apers_ei.employee_id = sel.assigner_id
			LEFT JOIN employee_info AS bpers_ei ON bpers_ei.employee_id = sel.assignee_id
			LIMIT 500";
	    
	    // Run the query 
	    $queryResult = DashboardPortal::$dashDb->query($dashTasksQuery);

	    // if we didn't get anything valid back, exit
	    if(!$queryResult)
	    {
		    //var_dump($queryResult);
		    echo DashboardPortal::$dashDb->error;
		    die();
	    }

	    $dashTasks;
	    while($taskRecord = $queryResult->fetch_array())
	    {
		if(empty($taskRecord["assigner_first_name"]))
		    $taskRecord["assigner_first_name"] = "";
		
		if(empty($taskRecord["assigner_last_name"]))
		    $taskRecord["assigner_last_name"] = "";
		
		if(empty($taskRecord["assignee_first_name"]))
		    $taskRecord["assignee_first_name"] = "";
		
		if(empty($taskRecord["assignee_last_name"]))
		    $taskRecord["assignee_last_name"] = "";
		
		if(empty($taskRecord["deadline_date"]))
		    $taskRecord["deadline_date"] = "";
		
		$taskAssigner = new DashPerson($taskRecord["assigner_first_name"] . " " . 
			$taskRecord["assigner_last_name"], 
			$taskRecord["apers_email"]);
		$taskAssignee = new DashPerson($taskRecord["assignee_first_name"] . " " . 
			$taskRecord["assignee_last_name"],
			$taskRecord["bpers_email"]);
		
		$dashTasks[] = new DashboardTask($taskRecord["external_id"],
			$taskRecord["description"], 
			$taskAssigner, $taskAssignee, 
			$taskRecord["deadline_date"]);   
	    }


	    $queryResult->close();	
	    
	    DashboardPortal::disconnect();
	    
	    return $dashTasks;
    }
}

?>
