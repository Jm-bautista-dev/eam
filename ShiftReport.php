<?php
include 'db_connect.php';
session_start();
date_default_timezone_set('Asia/Manila');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shift Reports</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      min-height: 100vh;
      background-color: #f4f6f8;
    }
    .main-content {
      flex: 1;
      padding: 20px 40px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .profile {
      position: relative;
      cursor: pointer;
    }
    .profile img {
      border-radius: 50%;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 50px;
      background-color: #fff;
      border: 1px solid #ddd;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
      z-index: 1000;
    }
    .dropdown a {
      display: block;
      padding: 10px 20px;
      color: #333;
      text-decoration: none;
    }
    .dropdown a:hover {
      background-color: #f0f0f0;
    }
    .section-box {
      background-color: #fff;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .section-header {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 12px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #f9f9f9;
    }
    .view-btn {
      background-color: #0052D4;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    .view-btn:hover {
      background-color: #003eaa;
    }

    form label {
      font-weight: 500;
      margin-right: 8px;
      color: #333;
    }
    input[type="date"], select {
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 10px;
      background-color: #f9f9f9;
      font-size: 14px;
      color: #333;
      outline: none;
      transition: border 0.3s, box-shadow 0.3s;
    }
    input[type="date"]:focus, select:focus {
      border-color: #0052D4;
      box-shadow: 0 0 4px rgba(0, 82, 212, 0.4);
    }
    select {
      appearance: none;
      background-image: url('data:image/svg+xml;utf8,<svg fill="%230052D4" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px 16px;
    }
    form > * {
      margin-right: 12px;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <ul class="nav-links">
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="employees.php">Employees</a></li>
    <li><a href="schedule.php">Schedule</a></li>
    <li class="active"><a href="ShiftReport.php">Shift Reports</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="header">
    <div><h3>Shift Reports</h3></div>
    <div class="profile">
      <img src="https://via.placeholder.com/40" alt="Admin" onclick="toggleDropdown()" />
      <div class="dropdown" id="profileDropdown">
        <a href="#">My Profile</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="content">
    <form method="POST" action="export_report.php" style="margin-bottom: 20px;">
      <label for="date">Select Date:</label>
      <input type="date" name="date" required>

      <label for="format">Export Format:</label>
      <select name="format" required>
        <option value="excel">Excel</option>
      </select>

      <button type="submit" class="view-btn">Export</button>
    </form>

    <div class="section-box">
      <div class="section-header">Shift Records</div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Shift</th>
              <th>Date</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>Status</th>
              <th>Tardiness</th>
              <th>Work Duration</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $today = date('Y-m-d');

            $query = $conn->query("
              SELECT 
                e.name, 
                s.shift_name, 
                a.date, 
                a.time_in, 
                a.time_out, 
                a.status, 
                a.tardiness, 
                a.work_duration 
              FROM tbl_employee e
              LEFT JOIN tbl_attendance a ON a.employee_id = e.id AND a.date = '$today'
              LEFT JOIN tbl_shift s ON a.shift_id = s.id
              ORDER BY e.name ASC
            ");

            while ($row = $query->fetch_assoc()) {
              $time_in = $row['time_in'] ?? '';
              $time_out = $row['time_out'] ?? '';
              $status = isset($row['time_in']) ? ($row['status'] ?? '') : ''; // Blank if no attendance
              $tardiness = $row['tardiness'] ?? '';
              $work_duration = $row['work_duration'] ?? '';
              $shift = $row['shift_name'] ?? 'N/A';

              echo "<tr>
                <td>{$row['name']}</td>
                <td>{$shift}</td>
                <td>{$today}</td>
                <td>{$time_in}</td>
                <td>{$time_out}</td>
                <td>{$status}</td>
                <td>{$tardiness}</td>
                <td>{$work_duration}</td>
              </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
