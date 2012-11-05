<?php

class TeamworkPortal
{
	/**
	 * Performs a get request of Teamwork
	 * @param $query The query string
	 * @return Returns the result if it succeeded and false otherwise.
	 */
	public static function getQuery($query) 
	{
		global $api_keys,$_SESSION;
		if(!isset($_SESSION['api_key']) || $_SESSION['api_key'] == "")
			$credentials = $api_keys["luke's"]["teamwork"].":xxx";
		else
			$credentials = $_SESSION['api_key'].":xxx";
		$ch = curl_init("http://byuis.teamworkpm.net/" . $query);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
						"Accept: application/xml",
						"Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, $credentials);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HEADER, true);
		$result = curl_exec($ch);
		if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
		{
			echo "ERROR: Teamwork API Call failed!\n"."HTTP Code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE)."\nResult: ".$result;
			exit;
		}
		curl_close($ch);
		return $result;
	}
	
	public static function getData($query,$formatResult=true)
	{
		//echo "Getting data for query: ".$query."\n";
		$result = self::getQuery($query);
		echo $result;
		return json_decode(($formatResult)?hyphenToUnderscore($result):$result);
	}
	
	public static function postQuery($query,$verb)
	{
		global $api_keys;
	}
}

/*
 * Utility function for replacing all underscores with hyphens in 
 * json variable names
 */
function hyphenToUnderscore($jsonString)
{
	return str_replace("-", "_", $jsonString);
}

?>
