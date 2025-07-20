<?php
$host = 'localhost';
$db = 'eam_db';
$user = 'root';
$pass = ''; // or your MySQL password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
