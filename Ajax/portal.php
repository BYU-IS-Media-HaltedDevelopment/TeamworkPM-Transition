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
$max_requests_per_second = 2;	//	120 per min = 2 per sec. see http://developer.teamworkpm.net/introduction#authentication

$ret_json = "{";

$ret_json .= "\"request\": {\"status\":\"ready\",\"json_throttler\":".json_encode($throttle_obj)."}";
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