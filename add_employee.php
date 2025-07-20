<?php
include 'db_connect.php';

$name = $_POST['name'];
$username = $_POST['username'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];
$department_id = $_POST['department_id'];
$status = $_POST['status'];
$password = 'jmbautista'; // Default password

$query = "INSERT INTO tbl_employee (name, username, email, mobile, department_id, status, password) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssiss", $name, $username, $email, $mobile, $department_id, $status, $password);
$stmt->execute();

echo "Employee added successfully!";
?>
