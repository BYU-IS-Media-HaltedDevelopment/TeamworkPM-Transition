<?PHP
function getDashboardData($which_query, $users_to_gather=array("-1"), $return_type="json") {
	switch($which_query) {
		case "user_specific_tasks":
			$select_sql = "
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
						(ta.assignee_id IN (".implode($users_to_gather).") OR ta.assigner_id IN (".implode($users_to_gather).")) AND
						t.closed_date IS NULL AND
						ta.marked_complete = 'N'
						LIMIT 500
					) AS sel
				LEFT JOIN processes_in_production AS pip ON sel.processes_in_production_id = pip.id
				LEFT JOIN processes AS p ON pip.process_id = p.process_id
				LEFT JOIN tasks AS t ON pip.process_id = t.process_id AND t.curr_task = sel.task_id
				LIMIT 500
			";
		break;
		
		case "get_user_email":
			$select_sql = "SELECT p.first_name, p.last_name,  ei.work_email FROM person AS p, employee_info AS ei WHERE p.person_id = ei.employee_id AND ei.`status` = 'active' AND ei.work_email <> '' AND ei.work_email IS NOT NULL";
		break;
		
		case "get_course_listing":
			$select_sql = "
				SELECT 
					course_name, course_num 
				FROM  
					course 
				WHERE 
					course_name IS NOT NULL AND
					course_name <> '' AND
					course_num IS NOT NULL AND
					course_num <> ''";
		break;
		
		default:
		break;
	}
	
	//	I'm making use of the mysqli object ... it's nice.
	$mysqli = new mysqli("isdbpro.byu.edu", "luke80", "changeme", "queue");
	if($mysqli->connect_errno) {
		printf("Connect failed: %s\n", $mysqli->connect_error);
	}
	$query_result = $mysqli->query($select_sql);
	$mysqli->close();
	if(strtolower($return_type) != "json")
		return $query_result;
	else {
		$return_json = json_encode($query_result);
		$query_result->close();
		return $return_json;
	}
}
?>