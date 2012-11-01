<?php

require_once 'TeamworkProjectManager.php';

switch ($_GET["action"]) 
{
    case "get_projects":
	$teamProjManager = TeamworkProjectManager::getInstance();
	$teamProjs = $teamProjManager->getProjects();
	$teamProjArrays = array();
	for($i = 0; $i < length($teamProjs); $i++)
	    $teamProjArrays[] = $teamProjs[$i]->exportToArray();
	
	echo json_encode($teamProjArrays);
    break;

    default:
	break;
}

?>


