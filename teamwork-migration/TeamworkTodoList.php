<?php
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
		    	return $todoItem;
		}
	    }
	    
	    return null;
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
