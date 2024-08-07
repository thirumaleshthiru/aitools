<?php
// db.php
$host = 'localhost';
$usernamee = 'root';
$password = '';
$database = 'ai_tools';
$port = 3307;

$mysqli = new mysqli($host, $usernamee, $password, $database, $port);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
