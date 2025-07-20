<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['add_shift'])) {
    // Auto-generate shift_id like SHIFT-001
    $result = $conn->query("SELECT MAX(id) AS max_id FROM tbl_shift");
    $row = $result->fetch_assoc();
    $nextId = ($row['max_id'] ?? 0) + 1;
    $generatedShiftId = 'SHIFT-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO tbl_shift (shift_id, shift_name, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $generatedShiftId, $_POST['shift_name'], $_POST['start_time'], $_POST['end_time']);
    $stmt->execute();
  }

  if (isset($_POST['edit_id'])) {
    $stmt = $conn->prepare("UPDATE tbl_shift SET shift_id=?, shift_name=?, start_time=?, end_time=? WHERE id=?");
    $stmt->bind_param("ssssi", $_POST['shift_id'], $_POST['shift_name'], $_POST['start_time'], $_POST['end_time'], $_POST['edit_id']);
    $stmt->execute();
  }

  if (isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM tbl_shift WHERE id=?");
    $stmt->bind_param("i", $_POST['delete_id']);
    $stmt->execute();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Schedule - Attendance System</title>
  <link rel="stylesheet" href="employees.css">
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f4f6f8; }
    .form-box {
      max-width: 500px;
      margin-bottom: 30px;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    input[type="text"], input[type="time"] {
      width: 100%;
      padding: 8px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .save-btn, .delete-btn, .edit-btn {
      padding: 8px 16px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-weight: 600;
    }
    .save-btn {
      background: linear-gradient(to right, #007bff, #0052d4);
      color: #fff;
    }
    .save-btn:hover {
      background: linear-gradient(to right, #0052d4, #007bff);
    }
    .edit-btn {
      background: #28a745;
      color: #fff;
      margin-right: 6px;
    }
    .delete-btn {
      background: #dc3545;
      color: #fff;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      margin-top: 20px;
      border-radius: 8px;
      overflow: hidden;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th {
      background: #f9f9f9;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <ul class="nav-links">
    <li><a href="Dashboard.php">Dashboard</a></li>
    <li><a href="employees.php">Employees</a></li>
    <li class="active">Schedule</li>
    <li><a href="ShiftReport.php">Shift Reports</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="header">
    <div><h3>Schedule</h3></div>
    <div class="profile">
      <img src="https://via.placeholder.com/40" alt="Admin" onclick="toggleDropdown()" />
      <div class="dropdown" id="profileDropdown">
        <a href="#">My Profile</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="form-box">
      <h3>Create Shift</h3>
      <form method="POST">
        <!-- shift_id is auto-generated, so we remove manual input -->
        <input type="text" name="shift_name" placeholder="Shift Name" required>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <button type="submit" name="add_shift" class="save-btn">➕ Add Shift</button>
      </form>
    </div>

    <h3>Existing Shifts</h3>
    <table>
      <thead>
        <tr><th>Shift ID</th><th>Shift</th><th>Start</th><th>End</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php
        $edit_id = $_GET['edit'] ?? null;
        $shifts = $conn->query("SELECT * FROM tbl_shift ORDER BY shift_name ASC");
        while ($row = $shifts->fetch_assoc()) {
          if ($edit_id == $row['id']) {
            echo "<tr>
              <form method='POST'>
              <input type='hidden' name='edit_id' value='{$row['id']}'>
              <td><input type='text' name='shift_id' value='{$row['shift_id']}' required></td>
              <td><input type='text' name='shift_name' value='{$row['shift_name']}' required></td>
              <td><input type='time' name='start_time' value='{$row['start_time']}' required></td>
              <td><input type='time' name='end_time' value='{$row['end_time']}' required></td>
              <td>
                <button type='submit' class='save-btn'>💾 Save</button>
                <a href='schedule.php' class='delete-btn'>Cancel</a>
              </td>
              </form>
            </tr>";
          } else {
            echo "<tr>
              <td>{$row['shift_id']}</td>
              <td>{$row['shift_name']}</td>
              <td>{$row['start_time']}</td>
              <td>{$row['end_time']}</td>
              <td>
                <a href='?edit={$row['id']}' class='edit-btn'>✏️ Edit</a>
                <form method='POST' style='display:inline;'>
                  <input type='hidden' name='delete_id' value='{$row['id']}'>
                  <button type='submit' class='delete-btn' onclick=\"return confirm('Delete this shift?')\">🗑️ Delete</button>
                </form>
              </td>
            </tr>";
          }
        }
        ?>
      </tbody>
    </table>
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
