<?php
$servername = "boq-db-ser";
$username = "userM31";
$password = "KqoSH75wF4OLJkGX";
$dbname = "boqdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
