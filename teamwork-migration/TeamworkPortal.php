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
	global $api_keys;
	$credentials = $api_keys["luke's"]["teamwork"]."xxx";
	$ch = curl_init("http://byuis.teamworkpm.net/" . $query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
					"Accept: application/xml",
					"Content-Type: text/xml; charset=utf-8"));
	curl_setopt($ch, CURLOPT_USERPWD, "cut527march:xxx");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);		
    }
    
    public static function postQuery($query)
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
