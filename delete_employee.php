<?php
include 'db_connect.php';

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM tbl_employee WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  echo "Employee deleted successfully.";
} else {
  echo "Error deleting employee.";
}
?>
