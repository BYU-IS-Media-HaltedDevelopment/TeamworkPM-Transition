<?PHP
/*
	This function is intended to help throttle anything
	
	To use it you pass a call to the function with a
	max/sec value (which currently defaults to the
	TeamworkPM max of 2/sec) and it tells you whether 
	or not you've been throttled.
	
	A 2nd parameter can be added to pause the execution
	until the throttling limit has passed.
*/
function throttler($max_per_second=2,$pause_execution=false,$return_object=false) {
	$throttle_file = "throttle-counter.txt";
	$fh = fopen($throttle_file, "a+");
	if (flock($fh, LOCK_EX)) {  // acquire an exclusive lock
		$throttle_file_contents = fread($fh, filesize($throttle_file));
		$throttle_obj = json_decode($throttle_file_contents);
		if(is_null($throttle_obj)) {
			$throttle_obj = (object) '';
			$throttle_obj->mtime = gettimeofday(true);
			echo "{\"LOCAL_ERROR\":\"Failed parsing json\"}";
			exit;
		}
		
		$throttle_it = ( ($throttle_obj->mtime + (1/$max_per_second) ) > ( gettimeofday(true) * 1 ) )?true:false;
		
		$json_string =	"{";
		$json_string .=	"\"mtime\":\"" . gettimeofday(true) . "\"";
		$json_string .=	"}";
		ftruncate($fh, 0);
		fwrite($fh, $json_string);
		$throttle_obj->mtime_last = $throttle_obj->mtime;
		//echo $json_string;
	} else {
		echo "{\"LOCAL_ERROR\":\"Throttle file lock failed.\"}";
		exit;
	}
	//	There seems to be a small chance that the amount of time paused will be too small
	//	for this case I've added in a .02 increase in the formula to ensure we cover our back
	$throttle_obj->pause_time = ( ( $throttle_obj->mtime_last + (1.02/$max_per_second )  ) - (gettimeofday(true)*1) );
	
	if($pause_execution === true && $throttle_it && $throttle_obj->pause_time > 0) {
		usleep(1000000 * $throttle_obj->pause_time);
		//echo "<!--	Throttle limiter paused execution for ".($pause_time/1000000)." sec	-->\n";
		if(( ( $throttle_obj->mtime_last + (1/$max_per_second)  ) - (gettimeofday(true)*1) ) <= 0) {
			$throttle_it = false;
			$throttle_obj->mtime = gettimeofday(true);
			$throttle_obj->mtime_available = ( $throttle_obj->mtime + (1.02/$max_per_second )  );
		} else {
			echo "{\"LOCAL_ERROR\":\"Throttle limiter didn't pause long enough\"}";
			exit;
		}
	}
	fclose($fh);
	if($return_object) {
		return $throttle_obj;
	} else if(!$throttle_it) {
		return true;
	} else {
		return false;
	}
}
?>