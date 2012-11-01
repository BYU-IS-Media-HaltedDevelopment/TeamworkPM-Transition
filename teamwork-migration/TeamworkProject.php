<?php

require_once "TeamworkTodoList.php";

/*
Represents a TeamworkProject
*/
class TeamworkProject
{
	private $id;		// teamwork id for the project
	private $name;		// name of the project
	private $todoLists; 	// list of itmes to do for this project

	/*
	Constructor
	*/
	public function __construct($id, $projName)
	{
		$this->id = $id;

		// sanitize the name
                $matches = null;
		if(preg_match('/.*-...-.../', $projName, $matches, 0, 0))
                    $this->name = $matches[0];
                else
                    $this->name = $projName;
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
	
	public function getTodoItemBySimilarDescrip($descrip)
	{   
	    if(!$this->hasTodoList())
		return;
	   
	    foreach($this->todoLists as $todoList)
	    {
		$searchResult = $todoList->getTodoItemBySimilarDescrip($descrip);
		if($searchResult != null)
		    return $searchResult;
	    }
	}
	
	private function hasTodoList() 
	{
	    if(!isset($this->todoLists))
		return false;
	    else
		return true;
	}

	/*
	Loads the TodoList from Teamwork
	*/
	public function loadTodoList()
	{
		//echo "Loading todo list! <br />";	
		// echo $this->id . "<br />";

		global $api_keys;
		$credentials = $api_keys["luke's"]["teamwork"]."xxx";
		$ch = curl_init("http://byuis.teamworkpm.net/projects/" . $this->id . "/todo_lists.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
						"Accept: application/xml",
						"Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERPWD, "cut527march:xxx");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$todoListJson = hyphenToUnderscore(curl_exec($ch));
		$todoLists = json_decode($todoListJson)->todo_lists;
                for($i = 0; $i < count($todoLists); $i++)
                {
                    $this->todoLists[] = new TeamworkTodoList($todoLists[$i]->project_id);
                    $todoItems = $todoLists[$i]->todo_items;
                    for($j = 0; $j < count($todoItems); $j++)
                        $this->todoLists[$i]->
			    addTodoItem(new TodoItem($todoItems[$j]->description));
                }
	}

	/*
	Indicates whether or not the list is loaded
	*/
	public function todoListLoaded()
	{
		return isset($this->todoList);	
	}
	
	/*
	 * Exports the object to a simple array
	 */
	public function exportToArray()
	{
	    $projectArray = array();
	    $projectArray["id"] = $this->id;
	    $projectArray["name"] = $this->name;
	    
	    return $projectArray;
	}
}
?>
