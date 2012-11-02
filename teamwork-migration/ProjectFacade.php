<?php
ini_set('display_errors', '1');
 
require_once 'TeamworkProjectManager.php';

switch ($_GET["action"]) 
{
    case "get_projects":
		$teamProjArrays = array();
		$teamProjManager = TeamworkProjectManager::getInstance();
		$teamProjs = $teamProjManager->getProjects();
		for($i = 0; $i < count($teamProjs); $i++)
			$teamProjArrays[] = $teamProjs[$i]->exportToArray();
		echo json_encode($teamProjArrays);
    break;

    default:
	break;
}

?>


