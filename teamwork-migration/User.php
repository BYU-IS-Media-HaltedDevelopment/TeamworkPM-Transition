<?php
if(session_id())
	session_destroy();

session_start();
assert('isset($_POST["dash_user"])');
assert('isset($_POST["api_key"])');

$_SESSION["dash_user"] = $_POST["dash_user"];
$_SESSION["api_key"] = $_POST["api_key"];
$_SESSION["id"] = session_id();
echo json_encode($_SESSION);
?>