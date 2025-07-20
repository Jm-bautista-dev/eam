<?php
session_start();
include 'db_connect.php';

$email = $_POST['adminEmail'];
$password = $_POST['adminPassword'];

$query = "SELECT * FROM tbl_admin WHERE email = ? AND password = SHA2(?, 256)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['admin'] = $email;
    header("Location: dashboard.php");
    exit(); // 🔒 Always use exit after header redirect
} else {
    echo "Invalid email or password.";
}
?>
