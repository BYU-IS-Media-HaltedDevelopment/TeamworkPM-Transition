<?PHP
$debug_info = "";

function sendTeamworkPM_APICall($path, $post_data=null,$verb=null,$credentials="NONE:xxx") {
	global $api_keys;
	if($credentials == "NONE:xxx") $credentials = $api_keys["luke's"]['teamwork'];
	if(strpos($credentials, ":xxx") == -1) {
		$credentials = $credentials.":xxx";
	}
	/*
	set_time_limit(0);		//	Stop timeouts
	$debug_info .= "Sleeping for 0.5 seconds
 ".date('h:i:s.u')."
--\n";
	usleep(500000);			//	Sleep for 0.5 sec to avoid the TeamWork rate limiter (120 requests / min) 
	$debug_info .= "Slept for 0.5 seconds
 ".date('h:i:s.u')."
--\n";
	*/
	//	This PHP script accesses the TeamWork PM api
	
	$ret = array(
		'status'	=>	0,
		'new_id'	=>	NULL
	);
	
	$ch = curl_init();
	
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, "http://byuis.teamworkpm.net/".$path);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_NOBODY, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	if(!is_null($verb) && trim($verb) != "" && (strtolower($verb) == "put" || !in_array(strtolower($verb),array("get","post")))) {
		$debug_info .= "Performing request to "."http://byuis.teamworkpm.net/".$path." with verb:
".$verb."
--\n";
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($verb));
		/*
		curl_setopt($ch, CURLOPT_PUT, true);
		$time = time();
		$infile_file_name = "tmp/infile.".$time.".".substr(md5($post_data),22).".tmp";
		$fp = fopen($infile_file_name, "w+");
		fwrite($fp, $post_data);
		//fwrite($fp, "");
		fclose($fp);
		curl_setopt($ch, CURLOPT_INFILE, $infile_file_name);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($infile_file_name));
		*/
		if(!is_null($post_data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array (
					"Content-Type: application/xml; charset=utf-8",
					'Content-Length: ' . strlen($post_data),
					"Expect: 100-continue",
					"Authorization: Basic " . base64_encode($credentials)
				));
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array (
					"Authorization: Basic " . base64_encode($credentials)
				));
		}
	} else if($post_data != null && strlen($post_data) > 28) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
				"Content-Type: text/xml; charset=utf-8",
				'Content-Length: ' . strlen($post_data),
				"Expect: 100-continue",
				"Authorization: Basic " . base64_encode($credentials)
			));
	}else {
		curl_setopt($ch, CURLOPT_GET, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
				"Authorization: Basic " . base64_encode($credentials),
				"Cache-Control: no-cache",
				"Pragma: no-cache"
			));
	}
	$response = curl_exec($ch);
	$ret['status'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	$ret['header'] = $header;
	$ret['body'] = $body;
	if(preg_match("/[\r\n]Location:[^\r\n]+\/(\d+)[\r\n]/", $header, $matches) > 0) {
		$ret['new_id'] = $matches[1];
	} else {
		//echo "Error in result. Data missing from response!<br/>\nStatus: ".$ret['status']."<br/>\n".$ret['body'];
		$ret['new_id'] = null;
	}
	curl_close($ch);
	return $ret;
}

function getTeamworkPMData($data_type="projects",$id=38839,$flags="") {
	$debug_info = "";
	$url = formTeamworkPMurl($data_type,"json",$id);
	$debug_info .= "Formed request url: ".$url."\n";
	$result_query = sendTeamworkPM_APICall($url.((strlen($flags)>0)?"?".$flags:""));
	if(substr($result_query['status'],0,1) != "2") {
		echo "Unexpected result returned from Teamwork API.<br/>\n";
		echo "Requested: ".$url."<br/>\n";
		echo "Status: ".$result_query['status']."<br/>\n";
		return false;
	}
	//$debug_info .= "".$result_query["body"]."-\n";
	$result_data = parseTeamworkResponse($result_query["body"], "json");
	$result_data->header = explode("\n",$result_query['header']);
	$result_data->debug_data = $debug_info;
	return $result_data;
}
function putTeamworkPMData($data,$data_type="create_todo",$id=38839,$format="json") {
	$debug_info = "";
	if(is_array($data) || is_object($data)) {
		$data = json_encode($data);
	}
	switch ($data_type) {
		case "create_todo-list":
			$url = formTeamworkPMurl("projects",$format,$id);
			$debug_info .= "".$url."
-\n";
			$result = sendTeamworkPM_APICall($url,$data,"POST");
		break;
		case "delete_todo-list":
			$result = sendTeamworkPM_APICall("todo_lists/".$id.".xml",$data,"DELETE");
		break;
		case "delete_todo-item":
			$result = sendTeamworkPM_APICall("todo_items/".$id.".xml",$data,"DELETE");
		break;
		case "delete_time-entry":
			$url = formTeamworkPMurl($data_type,$format,$id);
			$debug_info .= "".$url."
-\n";
			$result = sendTeamworkPM_APICall($url,$data,"DELETE");
		break;
		case "project_todo-list":
			$result = sendTeamworkPM_APICall("projects/".$id."/todo_lists.".$format,$data);
		break;
		case "create_todo-item":
			$url = formTeamworkPMurl("todo_item",$format,$id);
			$debug_info .= "".$url."
-\n";
			$result = sendTeamworkPM_APICall($url,$data,"POST");
		break;
		case "update_todo-item":
			$action = "todo_items/".$id.".".$format;
			if(strtolower($format) == "json") {
				$data = $request_json;
			}
			/*
			$data_obj = json_decode($request_json);
			$Serializer = new XMLSerializer();
			$data_xml = $Serializer->generateValidXmlFromObj($data_obj,"request","todo-item");
			$data_xml = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>", "", $data_xml);
			$data_xml = "<request>
				<todo-item>
					<notify type=\"boolean\">false</notify>
					<responsible-party-id>50532</responsible-party-id>
					<content>Submit Independent Contractor checklist to Account Payable. Check this off if approved.</content>
					<description>Submit Independent Contractor checklist to Account Payable. Check this off if approved.</description>
				</todo-item>
			</request>";

			
			$data = $data_xml;
			//$action = "todo_items/".$id.".xml";
			$action = "todo_items/1057402.xml";
			*/
			$debug_info .= "Requesting ".$action."
--\n";
			$result = sendTeamworkPM_APICall($action,$data,"PUT");
		break;
		case "add_todo-item-time":
			$url = formTeamworkPMurl($data_type,$format,$id);
			$debug_info .= "".$url."
-\n";
			$result = sendTeamworkPM_APICall($url,$data,"POST");
		break;
		case "add_project":
			$result = sendTeamworkPM_APICall("projects.".$format,$data,"POST");
		break;
		case "update_project":
			$result = sendTeamworkPM_APICall("projects/".$id.".".$format,$data,"PUT");
		break;
		default:
			echo "Default undefined (so far)<br/>\n";
			$result = array();
		break;
	}
	if(substr($result['status'],0,1) != 2) {
		echo "(".$result['status'].") There was an error putting your data!<br/>\n";
		$debug_info .= "Request body:
".$data."\n";
		$debug_info .= "Response body:
".$result['body']."\n";
	}
	$debug_info .= "Attempting to get data from the response\n";
	$result['response_object'] = parseTeamworkResponse($result['body'], "xml");
	$debug_info .= "Data gotten.\n";
	$result['header'] = explode("\n",$result['header']);
	foreach($result['header'] as $line_index => $header_line) {
		$header_var = explode(":",$header_line);
		if(count($header_var) > 1) {
			unset($result['header'][$line_index]);
			$result['header'][trim($header_var[0])] = trim($header_var[1]);
		}
	}
	$result['response_object']->debug_data = $debug_info;
	return $result;
}
function formTeamworkPMurl($task,$format="json",$id) {
	$file_name = "index";
	switch ($task) {
		case "project_list":
			$file_name = "projects";
			$url = $file_name.".".$format;
		break;
		case "project_list":
			$file_name = "projects";
			$url = $file_name.".".$format;
		break;
		case "project_todo-lists":
			 $url = "/projects/".$id."/todo_lists.".$format;
		break;
		case "todo_item":
			$url = "todo_lists/".$id."/todo_items.".$format;
		break;
		case "delete_todo-item":
			$url = "todo_items/".$id.".".$format;
		break;
		case "todo-item-times":
			$url = "todo_items/".$id."/time_entries.".$format;
		break;
		case "get_all-time":
			$url = "time_entries.".$format;
		break;
		case "add_todo-item-time":
			$url = "todo_items/".$id."/time_entries.".$format;
		break;
		case "delete_time-entry":
			$url = "time_entries/".$id.".".$format;
		break;
		case "user_list":
			if(!is_null($id)) {
				$url = "projects/".$id."/people.".$format;
			} else {
				$url = "companies/21601/people.".$format;
			}
		break;
		case "projects":
		default:
			$data_type = "projects";
			$file_name = "todo_lists";
			$url = $data_type."/".$id."/".$file_name.".".$format;
		break;
		
	}
	return $url;
}
function parseTeamworkResponse($response_text, $format="json") {
	//echo "\n1:\n";
	$ret = "";
	try {
		$response_text = trim($response_text);
		$ret = preg_replace('/(\<|[,{]")([^\s"-]+)\-([^\s"-]+)?-?/', '$1$2_$3', $response_text);
		//$debug_info .= "".substr($ret,0,1)."-\n";
		//echo "Case 1 - ".($format == "json" && substr($ret, 0, 1) == "{")."\n";
		//echo "Case 2 - ".(substr($ret, 0, 1) == "<")."\n";
		if($format == "json" && substr($ret, 0, 1) == "{") {
			if($ret != "") {
				$debug_info .= "JSON Response Received!	-\n";
				//$debug_info .= "\"-\" removed?: ".$new_body."-\n";
			} else {
				$debug_info .= "Error"."
-\n";
			}
			$ret = json_decode($ret, false);
		} else if(substr($ret, 0, 1) == "<") {
			//	This section the response is XML
			$debug_info .= "XML Response Received!	-\n";
			echo $ret;
			try {
				$ret = simplexml_load_string($ret);
			} catch(Exception $err) {
				echo "\n<br/>simplexml_load_string error:\n";
				echo $err->getMessage();
				echo "<br/>\n\n";
			}
		} else {
			$ret = array();
			$ret['body'] = $response_text;
			$ret['status'] = "0";
			$ret['header'] = "";
			//$ret['status'] = "0";
		}
	} catch(Exception $err) {
		$debug_info .= "Error!!\n";
		$debug_info .= "".$err->getMessage()."\n";
	}
	//echo "\n9:\n";
	return $ret;
}

function pruneObjectforPost($obj_to_prune) {
	//	In this section I attempted an idea for using JSON-produced objects
	//	The basic idea was to use the original structure - modify the things that need modification
	//	then send the updated object back. However, including properties that can't be updated makes
	//	the api fail, so the tree needs to be pruned.
	//	I think this path should be finished, since the basic idea of it is good.
	//	To that end I think I'll make it its own function.
	$request_obj = json_decode(str_replace("-", "_", "{\"todo-item\": ".$obj_to_prune."}"), false);
	$acceptable_properties = array("content","notify","description","due_date","priority","responsible_party_id");
	$required_properties = array("content"=>"","notify"=>"false","description"=>"","private"=>"false","priority"=>"medium");
	foreach($request_obj as $key1 => $val1) {
		if($key1 == "todo_item") {
			foreach($val1 as $key2 => $val2) {
				if(!in_array($key2, $acceptable_properties)) {
					unset($request_obj->todo_item->$key2);
				}
			}
		}
	}
	foreach($required_properties as $prop => $default_value) {
		if(!isset($request_obj->todo_item->$prop)) {
			$request_obj->todo_item->$prop = $default_value;
		}
	}
	$request_json = str_replace("_", "-", json_encode($request_obj));
	
	return $obj_to_prune;
}
function changeHyphenationFromObjectPropertyTitles($obj, $remove=true) {
	$remove_char = ($remove)?"-":"_";
	$add_char = ($remove)?"_":"-";
	if($obj instanceof String) {
		$string_value = $obj;
	} else {
		$string_value = json_encode($obj);
	}
	$max_loops = 10000;
	$loop_count = 0;
	while(preg_match("/[\{\[,]\s*\"[\w\d]+".$remove_char."/i", $string_value, $matches) && $max_loops < $loop_count) {
		$string_value = preg_replace('/([\{\[,]\s*\"[\w\d]+)'.$remove_char.'/i', "\1".$add_char, $string_value);
		$loop_count++;
	}
	echo "LoopCount: ".$loop_count."\n";
	if($obj instanceof String) {
		return $string_value;
	} else {
		return json_decode($string_value);
	}
	return $obj;
}
/*
function addUsersToProject($project=38839,$users="ALL",$permissionToAdd="Admin") {
	if(!is_numeric($project)) {
		echo "Try to specify a TeamworkPM project ID next time!<br/>\n";
		exit;
	}
	$current_users_result = sendTeamworkPM_APICall("projects/".$project."/people.json");
	$current_users = json_decode($current_users_result["body"], true);
	print_r($current_users);
}
*/

class XMLSerializer {

    // functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/

    public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}
?>