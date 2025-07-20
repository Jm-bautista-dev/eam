<?php
include 'db_connect.php';
session_start();
date_default_timezone_set('Asia/Manila');

// Get real-time counts
$empCount = $conn->query("SELECT COUNT(*) AS total FROM tbl_employee")->fetch_assoc()['total'];
$deptCount = $conn->query("SELECT COUNT(*) AS total FROM tbl_department")->fetch_assoc()['total'];
$shiftCount = $conn->query("SELECT COUNT(*) AS total FROM tbl_shift")->fetch_assoc()['total'];

// Get employee rows
$employees = $conn->query("
  SELECT e.*, d.department_name 
  FROM tbl_employee e 
  LEFT JOIN tbl_department d ON e.department_id = d.id
  ORDER BY e.name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Attendance System</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <ul class="nav-links">
    <li class="active">Dashboard</li>
    <li><a href="employees.php">Employees</a></li>
    <li><a href="schedule.php">Schedule</a></li>
    <li><a href="ShiftReport.php">Shift Reports</a></li>
  </ul>
</div>

<!-- Main -->
<div class="main-content">
  <div class="header">
    <div><h3>Dashboard</h3></div>
    <div class="profile">
      <img src="https://via.placeholder.com/40" alt="Admin" onclick="toggleDropdown()" />
      <div class="dropdown" id="profileDropdown">
        <a href="#">My Profile</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="dashboard-container">
      <!-- Cards -->
      <div class="stats-cards">
        <div class="card">
          <div class="icon icon-blue">👥</div>
          <div class="card-content">
            <h3><?= $empCount ?> Employees</h3>
            <form method="GET" action="employees.php">
            <button class="view-btn">Manage</button>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="icon icon-green">🏢</div>
          <div class="card-content">
            <h3><?= $deptCount ?> Departments</h3>
            <button class="green">View</button>
          </div>
        </div>
        <div class="card">
          <div class="icon icon-gray">🕒</div>
          <div class="card-content">
            <h3><?= $shiftCount ?> Shifts</h3>
            <form method="GET" action="schedule.php">
            <button class="gray">Configure</button>
          </form>
          </div>
        </div>
      </div>

      <!-- Employee Table Section -->
      <div class="sections">
        <div class="section-box">
          <div class="section-header">Current Employees</div>
          <div style="overflow-x: auto;">
            <table style="width:100%; border-collapse: collapse;">
              <thead>
                <tr style="background:#f9f9f9;">
                  <th style="padding:12px; text-align:left;">Name</th>
                  <th style="padding:12px; text-align:left;">Username</th>
                  <th style="padding:12px; text-align:left;">Department</th>
                  <th style="padding:12px; text-align:left;">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $limit = 5; $shown = 0;
                $employees->data_seek(0); // Reset pointer if looped
                while($row = $employees->fetch_assoc()):
                  if (++$shown > $limit) break;
                ?>
                <tr>
                  <td style="padding:12px;"><?= $row['name'] ?></td>
                  <td style="padding:12px;"><?= $row['username'] ?></td>
                  <td style="padding:12px;"><?= $row['department_name'] ?? 'Unassigned' ?></td>
                  <td style="padding:12px;"><?= $row['status'] ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <form method="GET" action="employees.php">
            <button class="view-btn">View All</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Dropdown -->
<script>
function toggleDropdown() {
  const dropdown = document.getElementById('profileDropdown');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}
window.addEventListener('click', function (e) {
  const profile = document.querySelector('.profile');
  const dropdown = document.getElementById('profileDropdown');
  if (!profile.contains(e.target)) dropdown.style.display = 'none';
});
</script>

</body>
</html>
