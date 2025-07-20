<?php
include 'db_connect.php';

$id = $_POST['id'];
$name = $_POST['name'];
$username = $_POST['username'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];
$department_id = $_POST['department_id'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE tbl_employee SET name=?, username=?, email=?, mobile=?, department_id=?, status=? WHERE id=?");
$stmt->bind_param("ssssisi", $name, $username, $email, $mobile, $department_id, $status, $id);

if ($stmt->execute()) {
  echo "Employee updated successfully.";
} else {
  echo "Error updating employee.";
}
?>
