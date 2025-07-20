<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Step 1: Get employee info including department_id (we'll override it below)
$empQuery = $conn->prepare("SELECT id AS employee_id, username AS emp_name, email, mobile FROM tbl_employee WHERE id = ? LIMIT 1");
$empQuery->bind_param("i", $employeeId);
$empQuery->execute();
$empResult = $empQuery->get_result();
$employee = $empResult->fetch_assoc();

if (!$employee) {
    die("Employee not found.");
}

$empDisplayID = 'EMP' . $employee['employee_id'];
$loggedInName = $employee['emp_name'];

// ✅ Force all users to department ID = 1
$departmentId = 1;

// Step 2: Get department name using department_id
$deptQuery = $conn->prepare("SELECT department_name FROM tbl_department WHERE id = ? LIMIT 1");
$deptQuery->bind_param("i", $departmentId);
$deptQuery->execute();
$deptResult = $deptQuery->get_result();
$department = $deptResult->fetch_assoc();

$departmentName = $department ? $department['department_name'] : 'unassigned';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="topbar">
  <div class="logo">👤 E.A.M</div>
  <div class="profile-dropdown">
    <span onclick="toggleDropdown()"><?php echo htmlspecialchars($loggedInName); ?> ⬇️</span>
    <div id="dropdown" class="dropdown-content">
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="container">
  <div class="sidebar">
    <ul>
      <li class="active">My Profile</li>
      <li><a href="attendance.php">Attendance Form</a></li>
      <li><a href="#">Lobby</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="card">
      <h2>Profile</h2>
      <table>
        <tr><td>Employee ID</td><td><?php echo htmlspecialchars($empDisplayID); ?></td></tr>
        <tr><td>Employee Name</td><td><?php echo htmlspecialchars($employee['emp_name']); ?></td></tr>
        <tr><td>Email</td><td><?php echo htmlspecialchars($employee['email']); ?></td></tr>
        <tr><td>Phone</td><td><?php echo htmlspecialchars($employee['mobile']); ?></td></tr>
        <tr><td>Department</td><td><?php echo htmlspecialchars($departmentName); ?></td></tr>
      </table>
    </div>
  </div>
</div>

<script>
function toggleDropdown() {
  document.getElementById("dropdown").classList.toggle("show");
}
window.onclick = function(event) {
  if (!event.target.matches('.profile-dropdown span')) {
    var dropdown = document.getElementById("dropdown");
    if (dropdown && dropdown.classList.contains('show')) {
      dropdown.classList.remove('show');
    }
  }
}
</script>

</body>
</html>