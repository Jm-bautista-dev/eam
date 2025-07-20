<?php
include 'db_connect.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM tbl_employee WHERE id = $id");
$row = $result->fetch_assoc();

echo json_encode($row);
?>
