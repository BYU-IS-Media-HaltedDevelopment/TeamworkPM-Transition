<?PHP
/*
	This file is intended to be a portal to TeamworkPM
	As such it is basically a "pass through" type page
	which normalizes requests and simply outputs the
	result returned from TeamworkPM.
	
	It also performs basic throttling to eliminate the
	potential for tripping the rate limit on the API.
*/
require_once('throttle.lib.php');
require_once('TeamworkPM_api.lib.php');

$json_string = "{\"todo-lists\":[{\"project-id\":\"73292\",\"todo-items\":[],\"name\":\"General Tasks (If you don't want to make a new list for your task, put it here)\",\"description\":\"\",\"milestone-id\":\"\",\"uncompleted-count\":\"4\",\"complete\":false,\"private\":false,\"overdue-count\":\"4\",\"project-name\":\"00 - Adminstrative Overhead\",\"project_id\":\"73292\",\"tracked\":true,\"id\":\"208390\",\"position\":\"1994\",\"completed-count\":\"0\"},{\"project-id\":\"73292\",\"todo-items\":[],\"name\":\"Teamwork Training Videos (Tutorials?)\",\"description\":\"\",\"milestone-id\":\"\",\"uncompleted-count\":\"3\",\"complete\":false,\"private\":false,\"overdue-count\":\"0\",\"project-name\":\"00 - Adminstrative Overhead\",\"project_id\":\"73292\",\"tracked\":true,\"id\":\"208393\",\"position\":\"1995\",\"completed-count\":\"0\"},{\"project-id\":\"73292\",\"todo-items\":[],\"name\":\"TaskManager to TeamworkPM conversion\",\"description\":\"\",\"milestone-id\":\"\",\"uncompleted-count\":\"17\",\"complete\":false,\"private\":false,\"overdue-count\":\"0\",\"project-name\":\"00 - Adminstrative Overhead\",\"project_id\":\"73292\",\"tracked\":true,\"id\":\"208391\",\"position\":\"1996\",\"completed-count\":\"0\"},{\"project-id\":\"73292\",\"todo-items\":[],\"name\":\"Turnitin\",\"description\":\"\",\"milestone-id\":\"\",\"uncompleted-count\":\"1\",\"complete\":false,\"private\":false,\"overdue-count\":\"1\",\"project-name\":\"00 - Adminstrative Overhead\",\"project_id\":\"73292\",\"tracked\":true,\"id\":\"206716\",\"position\":\"1997\",\"completed-count\":\"1\"},{\"project-id\":\"73292\",\"todo-items\":[],\"name\":\"Global Course Corrections\",\"description\":\"\",\"milestone-id\":\"\",\"uncompleted-count\":\"6\",\"complete\":false,\"private\":false,\"overdue-count\":\"4\",\"project-name\":\"00 - Adminstrative Overhead\",\"project_id\":\"73292\",\"tracked\":true,\"id\":\"198919\",\"position\":\"2000\",\"completed-count\":\"1\"}],\"STATUS\":\"OK\"}";
$preObj = json_decode($json_string);
$newObj = changeHyphenationFromObjectPropertyTitles($preObj);
print_r($json_string);
echo "\n\n";
print_r(changeHyphenationFromObjectPropertyTitles($json_string));

$max_requests_per_second = 2;	//	120 per min = 2 per sec. see http://developer.teamworkpm.net/introduction#authentication

$ret_json = "{";

$ret_json .= "\"request\": {\"status\":\"ready\"}";	//	,\"json_throttler\":".json_encode($throttle_obj)."
if(isset($_POST) && !is_null($_POST)) {
	if($_POST['id'] == "") {
		$ret_json .= "\"LOCAL_ERROR\":\"Malformed request, aborted. id undefined\"";
	} else {
		switch(strtolower($_POST['method'])) {
			// If it was a teamwork call... 
			case "delete":
			case "post":
			case "put":
				//echo "<!-- Attempting to load JSON to TeamworkPM -->\n";
				if($throttle_obj = throttler($max_requests_per_second,true,true)) {
					$ret_json .= ",\"response\":".json_encode(putTeamworkPMData($_POST['data'],$_POST['action'],$_POST['id'],"json"));
				} else {
					$ret_json .=  "\"request\":{\"status\":\"not ready\"}";
				}
			break;
			
			case "get":
				//echo "<!-- Attempting to load JSON from TeamworkPM -->\n";
				if($throttle_obj = throttler($max_requests_per_second,true,true)) {
					$ret_json .= ",\"response\":".json_encode(getTeamworkPMData($_POST['action'], $_POST['id']));
				} else {
					$ret_json .=  "\"request\":{\"status\":\"not ready\"}";
				}

			break;
			
			// If it was a dashboard call...
			case "dashboard":
				echo "Attempting to get the the course listing";
				$ret_json .= ",\"response\":".json_encode(getDashboardData($_POST['action'], $_POST['user_id']));
			break;
			
			default:
				$ret_json .= ",\"LOCAL_ERROR\":\"Malformed request, aborted. method undefined\"";
			break;
		}
	}
} else {
	$ret_json .= "\"LOCAL_ERROR\":\"Request undefined... tell me what you needed.\"";
}
$ret_json .= "}";
echo $ret_json;
?>