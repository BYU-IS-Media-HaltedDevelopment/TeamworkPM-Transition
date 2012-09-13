<?PHP
/* Don't put this back. the limiter is missing from this lib.
require_once('TeamworkPM_api.lib.php');
*/
?><HTML>
	<HEAD>
		<TITLE>TeamworkPM data import utility</TITLE>
	</HEAD>
	<BODY>
<?PHP

//	I don't really want it to insert projects, but I do want it to know when there is one missing
//
//	This grabs the list and dumps it into an array and the names into a long string.
$results = getTeamworkPMData("project_list");
$project_array = array();
$projects = "";
foreach($results->projects as $index => $project_obj) {
	$project_array[$project_obj->name] = $project_obj;
	$projects .= "|".$project_obj->name."=".$project_obj->id;
}
$projects .= "|";
$projects = preg_replace('/[^\w\d\|\=]/', '', $projects);
//echo "<!-- Projects: ".$projects."	-->\n";

//	User IDs and names are gathered for assignments
//	It's worth noting that the full name must match for the Dashboard person to get matched up to the Teamwork one.
$results = getTeamworkPMData("user_list");
$people_array = array();
foreach($results->people as $index => $people_obj) {
	$people_array[$people_obj->first_name." ".$people_obj->last_name] = $people_obj;
}
echo "<!-- People:\n";
//print_r($people_array);
print_r(array_keys($people_array));
echo "\n-->\n";

//	Here we are gathering the Dashboard data regarding the tasks for:
$users_to_gather = array("Suzzanne Gerhart" => "2057", "Scott Gutke" => "553");
//	If you modify the above line - you WILL have to add LOTS of users to their respective projects.
//	The intention is that you will change this line, then run it with errors and get the list of users and projects to modify.
//
//	This query will get most of the information needed for their tasks, but not for their students`
//
//	Also - it is specifically limited to 500 tasks. If they have more than this the query will have to be modified.
//	Also - if they have more than 500 open tasks then they are probably not going to like their tasks getting pulled into teamwork until they shorten that list a little...
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
		(ta.assignee_id IN (".implode($users_to_gather).")) AND
		t.closed_date IS NULL AND
		ta.marked_complete = 'N'
		LIMIT 500
	) AS sel
LEFT JOIN processes_in_production AS pip ON sel.processes_in_production_id = pip.id
LEFT JOIN processes AS p ON pip.process_id = p.process_id
LEFT JOIN tasks AS t ON pip.process_id = t.process_id AND t.curr_task = sel.task_id
LIMIT 500
";
//	I'm making use of the mysqli object ... it's nice.
$mysqli = new mysqli("isdbpro.byu.edu", "luke80", "changeme", "queue");
if($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
} else {
	echo "<!-- DB Success -->\n";
}

//	I created this little array to give an output after the page runs telling you which users potentially need to be added to which project.
//	If you modified the target people, then odds are you'll have a large number of users to add to a large number of projects.
$new_projects_by_user = array();
if($query_result = $mysqli->query($select_sql)) {
	echo "<!-- Query returned. -->\n";
	echo "<!-- ".$query_result->num_rows." Rows Returned! -->\n";
	while($row = $query_result->fetch_assoc()) {
		//echo "<!--	Matching: ".'/\|('.preg_replace('/[^\w\d]/', '', $row['external_id']).'[^\|\=]*)=(\d+)\|/i'."	-->\n";
		//echo "<!--	".preg_match_all('/\|('.preg_replace('/[^\w\d]/', '', $row['external_id']).'[^\|\=]*)=(\d+)\|/i', $projects, $matches)."	-->\n";
		$course_name = $row['external_id']." (".getRevision($row['scope']).")";
		if(preg_match_all('/\|('.preg_replace('/[^\w\d]/', '', $row['external_id']).'[^\|\=]*)=(\d+)\|/i', $projects, $matches) > 0) {
			echo "<!-- Course Exists already. -->\n";
			echo "<!-- Name: ".$matches[1][0]." -->\n";
			echo "<!-- TWPM ID: ".$matches[2][0]." -->\n";
			//print_r($matches);
			$project_id = $matches[2][0];
		} else {
			echo "<!-- Course Doesn't Exist. -->\n";
			$course_description = $row['designer_first_name']." ".$row['designer_last_name']."\r\n";
			$course_description .= $row['course_title']."\r\n\r\n".$row['course_description']."";
			echo "<!-- Name: ".$course_name." -->\n";
			$put_xml = "
				<request>
					<project>
						<name>".$course_name."</name>
						<description>".$course_description."</description>
					</project>
				</request>";
			echo $put_xml."\n";
			echo "<h1>!#@!#@! Would Have Inserted Project!!! (But didn't) ".$course_name."</h1>\n";
			//$result = putTeamworkPMData($put_xml,"add_project",null,"xml");
			$project_id = $result['new_id'];
		}	//	End Else - course didn't exits (if course did exist)
		echo "<!-- Project Section Complete -->\n";
		if($project_id) {
			if(!is_null($row['process_name'])) {
				$task_list_keyword = $row['process_name'];
				$match_regex = '/^(.*('.$task_list_keyword.').*)$/i';
				echo "<!--	Checking for existing task list:	-->\n";
				$results = getTeamworkPMData("project_todo-lists", $project_id);
				//print_r($results);
				$task_list_found = false;
				$task_list_id = null;
				//echo "<!--	type of \$results:	".(gettype($results))."	-->\n";
				//echo "<!--	type of \$results->todo_lists:	".(gettype($results->todo_lists))."	-->\n";
				//echo "<!--	count \$results->todo_lists:	".(count($results->todo_lists))."	-->\n";
				//echo "<!--	matching:	".$match_regex."	-->\n";
				foreach($results->todo_lists as $index => $tasklist_obj) {
					if(preg_match_all($match_regex, $tasklist_obj->name, $matches) > 0) {
						echo "<!--	Task list exists!!!	-->\n";
						$task_list_found = true;
						$task_list_id = $tasklist_obj->id;
						break;
					} else {
						echo "<!--	No match on ".$tasklist_obj->name."	-->\n";
					}
					//print_r($tasklist_obj);
				}
				if($task_list_found) {
					echo "<!--	Insert task on found list: ".$task_list_id."	-->\n";
					//break;
				} else {
					echo "<!--	Create a task list for this task!	-->\n";
					$task_list_name = "(Imported - dashboard tasks DEL3) ".$row['process_name'];
					$task_list_description = "This task list was inserted by a script - it holds tasks directly copied from dashboard/task manager.\nThe assigner is now Luke Rebarchik, but in dashboard it was someone else.";
					$put_xml = "
					<request>
						<todo-list>
							<name>".$task_list_name."</name>
							<private type=\"boolean\">false</private>
							<tracked type=\"boolean\">false</tracked>
							<description>".$task_list_description."</description>
						</todo-list>
					</request>
					";
					$result = putTeamworkPMData($put_xml,"create_todo-list",$project_id,"xml");
					//print_r($result);
					$task_list_id = $result['new_id'];
					//break;
				}
				echo "<!--	Currently selected TWPM task-list id: ".$task_list_id."	-->\n";
				echo "<!--		Detect if this task already exists!!!	-->\n";
				$result = getTeamworkPMData("todo_item", $task_list_id);
				$task_exists = false;
				foreach($result->todo_items as $todo_index => $todo_item_obj) {
					echo "<!--		Comparing remote:	".substr(trim(preg_replace('/[^\w\d]/', '',$todo_item_obj->content)), 0, 50)."	-->\n";
					echo "<!--		to local:		".substr(trim(preg_replace('/[^\w\d]/', '',$row['description'])), 0, 50)."	-->\n";
					if(preg_replace('/[^\w\d]/', '',substr(strtolower(trim($todo_item_obj->content)), 0, 50)) == preg_replace('/[^\w\d]/', '',substr(strtolower(trim($row['description'])), 0, 50))) {
						echo "<!--		Task Found! No need to enter it.	-->\n";
						$task_exists = true;
						$task_id = $todo_item_obj->id;
						break;
					}
				}
				$task_contents = substr($row['description'], 0, 100).((strlen($row['description']) > 100)?"...":"");
				if($task_contents == "") {
					$task_contents = substr($row['process_task_description'], 0, 100).((strlen($row['process_task_description']) > 100)?"...":"");
					$row['description'] = $row['process_task_description'];
				}
				if($task_contents == "") {
					$task_contents = "No Description Provided! (Weird, I know)";
				}
				$task_duedate = date('Ymd', strtotime(((!is_null($row['deadline_date']) && $row['deadline_date'] != "" && strtoupper($row['deadline_date']) != "NULL")?$row['deadline_date']:"+1 week")));
				//echo "<!--		Due Date: ".$task_duedate."	-->\n";
				//echo "<!--		DB Due Date: ".$row['deadline_date']."	-->\n";
				//echo "<!--		Custom Due Date: ".strtotime("+1 week")."	-->\n";
				//break;
				$task_priority = (($row['priority'] >= 4)?"high":(($row['priority'] <= 2)?"low":"medium"));
				$select_additional_users = "
					SELECT
						sel.*,
						bd.hours_billed,
						bd.time_stamp,
						bd.description AS bd_description
					FROM
						(SELECT
						t.my_task_id,				t.description,	t.deadline_date,
						pip.id AS processes_in_production_id,
						ta.id AS task_assignment_id,				ta.marked_complete,
						apers.first_name AS assigner_first_name,	apers.last_name AS assigner_last_name,
						bpers.first_name AS assignee_first_name,	bpers.last_name AS assignee_last_name,		bpers.person_id AS assignee_db_id,
						pip.start_date,				pip.deadline
					FROM
						processes_in_production AS pip,
						my_tasks AS t,
						task_assignments AS ta,
						person AS apers,
						person AS bpers
					WHERE
						pip.id = t.processes_in_production_id AND
						ta.my_task_id = t.my_task_id AND
						apers.person_id = ta.assigner_id AND
						bpers.person_id = ta.assignee_id AND
						" . /*"pip.id = ".$row['processes_in_production_id']." */
						" t.my_task_id = ".$row['my_task_id']."
						) AS sel
					LEFT JOIN birddog AS bd ON sel.my_task_id = bd.my_task_id AND sel.assignee_db_id = bd.employee_id
				";
				echo "<!--			".$select_additional_users."	-->\n";
				$task_person = array($row['assignee_first_name']." ".$row['assignee_last_name'] => $people_array[$row['assignee_first_name']." ".$row['assignee_last_name']]->id);
				$task_description = "This task was originally assigned in Dashboard/Task Manager to ".$row['assignee_first_name']." ".$row['assignee_last_name']." by ".$row['assigner_first_name']." ".$row['assigner_last_name']." on ".date('F jS, Y', strtotime($row['entry_date']))."\r\n\n";
				$time_xml = array();
				if($query_result2 = $mysqli->query($select_additional_users)) {
					while($row2 = $query_result2->fetch_assoc()) {
						echo "<!--	Assigned Person Found!	";
						//print_r($row2);
						$condition = in_array($row2['assignee_first_name']." ".$row2['assignee_last_name'],array_keys($people_array));
						//echo "	find condition: ".$condition."	";
						$person = ($condition)?$people_array[$row2['assignee_first_name']." ".$row2['assignee_last_name']]->id:"73380";
						echo "Person: ".$row2['assignee_first_name']." ".$row2['assignee_last_name']." = ".$person;
						echo "	-->\n";
						$task_person[$row2['assignee_first_name']." ".$row2['assignee_last_name']] = $person;
						//$task_description .= tidy_repair_string(strip_tags(preg_replace('/\<br\/?\>/i', "\n","Assigned to ".$row2['assignee_first_name']." ".$row2['assignee_last_name']." on ".date('F jS, Y', strtotime($row2['start_date']))." due by ".date('F jS, Y', strtotime($row2['deadline']))."\n")), array('output-xhtml' => true, 'show-body-only' => true, 'doctype' => 'strict', 'drop-font-tags' => true, 'drop-proprietary-attributes' => true, 'lower-literals' => true, 'quote-ampersand' => true, 'wrap' => 0), 'raw');
						if(!is_null($row2['hours_billed']) && $row2['hours_billed'] != "") {
							//$task_description .= tidy_repair_string(strip_tags(preg_replace('/\<br\/?\>/i', "\n","".$row2['assignee_first_name']." ".$row2['assignee_last_name']." spent ".floor($row2['hours_billed']).":".round(60*($row2['hours_billed']-floor($row2['hours_billed'])))." on ".date('F jS, Y', strtotime($row2['time_stamp']))." on this task.\n")), array('output-xhtml' => true, 'show-body-only' => true, 'doctype' => 'strict', 'drop-font-tags' => true, 'drop-proprietary-attributes' => true, 'lower-literals' => true, 'quote-ampersand' => true, 'wrap' => 0), 'raw');
							$put_xml_time = "
							<request>
								<time-entry>
									<description>".((!is_null($row2['bd_description']))?strip_tags(preg_replace('/\<br\/?\>/i', "\n","\n".$row2['bd_description'])):strip_tags(preg_replace('/\<br\/?\>/i', "\n","\n".$row2['description'])))."</description>
									<person-id>".$person."</person-id>
									<date>".date('Ymd', strtotime($row2['time_stamp']))."</date>
									<hours>".floor($row2['hours_billed'])."</hours>
									<minutes>".round(60*($row2['hours_billed']-floor($row2['hours_billed'])))."</minutes>
									<time>".date('H:i', strtotime($row2['time_stamp']))."</time>
									<isbillable type=\"boolean\">yes</isbillable>
								</time-entry>
							</request>
							";
							if(!is_array($time_xml[$row2['assignee_first_name']." ".$row2['assignee_last_name']])) {
								$time_xml[$row2['assignee_first_name']." ".$row2['assignee_last_name']] = array();
							}
							$time_xml[$row2['assignee_first_name']." ".$row2['assignee_last_name']][] = $put_xml_time;
							if(!is_array($new_projects_by_user[$row2['assignee_first_name']." ".$row2['assignee_last_name']])) {
								$new_projects_by_user[$row2['assignee_first_name']." ".$row2['assignee_last_name']] = array();
							}
							if(!in_array($course_name, $new_projects_by_user[$row2['assignee_first_name']." ".$row2['assignee_last_name']])) {
								$new_projects_by_user[$row2['assignee_first_name']." ".$row2['assignee_last_name']][] = $course_name;
								//print_r($new_projects_by_user);
							}
						}
					}
					$query_result2->close();
				} else {
					echo "<!--		MySQL ERROR!?!	-->\n";
					echo "<!--		".$mysqli->error."	-->\n";
				}
				$task_description .= "\n".$row['description'];
				if($query_result3 = $mysqli->query("
					SELECT
						mtn.date,mtn.description,p.first_name,p.last_name
					FROM
						my_tasks_notes AS mtn
					LEFT JOIN person AS p ON p.person_id = mtn.employee_id
					WHERE my_task_id = ".$row['my_task_id']."
					ORDER BY mtn.date DESC
				")) {
					while($row3 = $query_result3->fetch_assoc()) {
						$task_description .= "\n".((!is_null($row3['first_name']))?$row3['first_name']." ".$row3['last_name']." on ".date('F jS, Y', strtotime($row3['date'])).":\n":"On ".date('F jS, Y', strtotime($row3['date'])).":\n").$row3['description']."\n";
					}
					$query_result3->close();
				} else {
					echo "<!--		No notes found for this task.	-->\n";
				}
				//echo "<!--		Persons: ".join(",",$task_person)."	-->\n";
				echo "<!--		Person TWPM id(s) found: ".implode(",",$task_person)."	-->\n";
				$task_contents = preg_replace('/\<br\/?\>/i', "\r\n",$task_contents);
				$task_contents = strip_tags($task_contents);
				$task_contents = html_entity_decode($task_contents);
				$task_contents = htmlspecialchars_decode($task_contents);
				$task_contents = htmlspecialchars_decode($task_contents);
				$task_contents = htmlspecialchars($task_contents,null,null,false);
				//$task_contents = tidy_repair_string($task_contents, array('output-xhtml' => true, 'show-body-only' => true, 'doctype' => 'strict', 'drop-font-tags' => true, 'drop-proprietary-attributes' => true, 'lower-literals' => true, 'quote-ampersand' => true, 'wrap' => 0), 'raw');
				$task_description = preg_replace('/\<br\/?\>/i', "\r\n",$task_description);
				$task_description = strip_tags($task_description);
				$task_description = html_entity_decode($task_description);
				$task_description = htmlspecialchars_decode($task_description);
				$task_description = htmlspecialchars_decode($task_description);
				$task_description = htmlspecialchars($task_description,null,null,false);
				//$task_description = tidy_repair_string($task_description, array('output-xhtml' => true, 'show-body-only' => true, 'doctype' => 'strict', 'drop-font-tags' => true, 'drop-proprietary-attributes' => true, 'lower-literals' => true, 'quote-ampersand' => true, 'wrap' => 0), 'raw');
				$put_xml = "
				<request>
					<todo-item>
						<content>".$task_contents."</content>
						".((count($task_person) > 1)?"<responsible-party-id>".implode(",",$task_person)."</responsible-party-id>":"<responsible-party-id>".implode(", ",$task_person)."</responsible-party-id>")."
						<notify type=\"boolean\">false</notify>
						<description>".$task_description."</description>
						<due-date type=\"integer\">".$task_duedate."</due-date>
						<private type=\"boolean\">false</private>
						<priority>".$task_priority."</priority>
					</todo-item>
				</request>";
				echo "<!--		".$put_xml."	-->\n";
				if($task_exists) {
					echo "<!--		Retrieve all the list item time records.	-->\n";
					$time_result = getTeamworkPMData("todo-item-times",$task_id);
					if(count($time_result->time_entries) == 0) {
						echo "<!--			No time entries found!!! -->\n";
					}
					foreach($time_result->time_entries as $entry_id => $time_entry_obj) {
						echo "<!--			Deleting time entry id: ".$time_entry_obj->id." -->\n";
						$result_timedelete = putTeamworkPMData(null,"delete_time-entry",$time_entry_obj->id);
					}
					/*
					echo "<!--		Deleting the task list item - to re-create it.	-->\n";
					$result = putTeamworkPMData(null,"delete_todo-item",$task_id);
					
					if(substr($result['status'],0,1) == "2") {
						echo "<!--		Deleted task id: ".$task_id."	-->\n";
						$task_exists = false;
					} else {
						echo "<!--		Failed to delete task id: ".$task_id."	-->\n";
					}
					*/
				}
				if(!$task_exists) {
					$result = putTeamworkPMData($put_xml,"create_todo-item",$task_list_id,"xml");
					//print_r($result);
					$task_id = $result['new_id'];
					if(substr($result['status'],0,1) == "2") {
						echo "<!--		Task created with id: ".$task_id."	-->\n";
						echo "<div>Task created with id: ".$task_id."</div>\n";
					} else
						echo "<!--		There must have been an error adding task!	-->\n";
					//echo $put_xml;
				} else {
					$result = putTeamworkPMData($put_xml,"update_todo-item",$task_id,"xml");
					//print_r($result);
					if(substr($result['status'],0,1) == "2") {
						echo "<!--		Task with id: ".$task_id." updated!	-->\n";
						echo "<div>Task with id: ".$task_id." updated!</div>\n";
					} else {
						echo "<!--		There must have been an error updating task!	-->\n";
						//print_r($result);
					}
					//echo $put_xml;
				}
				//print_r($time_xml);
				if(!is_null($task_id)) {
					foreach($time_xml as $time_person => $put_xml_array) {
						foreach($put_xml_array as $time_index => $put_xml) {
							echo "<!--		Inserting time record: ".$time_person." ".$time_index."	-->\n";
							$result_time = putTeamworkPMData($put_xml,"add_todo-item-time",$task_id,"xml");
							//echo "<!-- Result:\n";
							//print_r($result_time);
							//echo "-->\n";
							/*
							set_time_limit(0);		//	Stop timeouts
							echo "<!---\n sss Sleeping for 0.5 seconds\n ".date('h:i:s.u')."\n--->\n";
							usleep(500000);			//	Sleep for 0.5 sec to avoid the TeamWork rate limiter (120 requests / min) 
							echo "<!---\n sss Slept for 0.5 seconds\n ".date('h:i:s.u')."\n--->\n";
							*/
							if(substr($result_time['status'],0,1) == "2") {
								echo "<!--		Time entered with id: ".$result_time['response_object']->timelogid."	-->\n";
								echo "<div>Time entered with id: ".$result_time['response_object']->timelogid."</div>\n";
							} else {
								echo "<!--		There must have been an error adding time!	-->\n";
								echo "<div>Time not entered with id: ".$task_id."</div>\n";
							}
						}
					}
				} else {
					echo "<!--		Since the task couldn't be created (or wasn't) we're skipping inserting the time too. (".count($time_xml)." time entries not entered)	-->\n";
				}
				
				//break;		//	This break will stop the process after the first iteration
			} else {
				echo "<!--	Process was null from TM DB	-->\n";
			}
		} else {
			echo "<!-- Error: no project_id set! -->\n";
		}
	}	//	End While Loop
	$query_result->close();
}		//	End If query returned results
$mysqli->close();
function getRevision($scope_string) {
	$ret = "Undefined";
	switch ($scope_string) {
		case "Transition":
		case "Unknown":
			$ret = "???";
		break;
		case "New Course":
			$ret = "New";
		default:
			$ret = preg_replace('/\D/', '', $scope_string)."%";
		break;
	}
	return $ret;
}
/*
$projects = getTeamworkPMData("project_list");

foreach($projects->projects as $index => $project_obj) {
	$users = getTeamworkPMData("user_list",$project_obj->id);
	$user_names = array();
	foreach($users->people as $person_obj) {
		$user_names[] = $person_obj->first_name." ".$person_obj->last_name;
		echo $user_names[count($user_names)-1]." ".$person_obj->administrator."\n";
	}
	print_r($user_names);
	break;
}
*/

foreach($new_projects_by_user as $user_name => $user_project_array) {
	echo "<div>Add ".$user_name." to the following projects: <br/>\n";
	foreach($user_project_array as $project_name) {
		echo "	<div>".$project_name."</div>\n";
	}
	echo "</div>\n";
}
?>
	</BODY>
</HTML>
