<?php session_start();
assert('isset($_POST["dash_user"])');
assert('isset($_POST["api_key"])');

$_SESSION["dash_user"] = $_POST["dash_user"];
$_SESSION["api_key"] = $_POST["api_key"];
echo "Ok";
?>