<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "db_connect.php";

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Fetch employee name
$stmt = $conn->prepare("SELECT username FROM tbl_employee WHERE id = ?");
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$employeeName = $employee['username'] ?? 'Employee';

// Fetch shifts
$shifts = [];
$shiftQuery = $conn->query("SELECT id, shift_name, start_time, end_time FROM tbl_shift");
while ($row = $shiftQuery->fetch_assoc()) {
    $shifts[] = $row;
}

// Fetch today's attendance
$dateToday = date('Y-m-d');
$attendanceStmt = $conn->prepare("SELECT shift_id, time_in, time_out FROM tbl_attendance WHERE employee_id = ? AND date = ?");
$attendanceStmt->bind_param("is", $employeeId, $dateToday);
$attendanceStmt->execute();
$attendanceResult = $attendanceStmt->get_result();
$attendance = $attendanceResult->fetch_assoc();

$checkedIn = $attendance && !empty($attendance['time_in']);
$checkedOut = $attendance && !empty($attendance['time_out']);
$buttonsDisabled = $checkedOut;
$selectedShiftId = $attendance['shift_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Form</title>
  <link rel="stylesheet" href="index.css" />
  <link rel="stylesheet" href="attendance.css" />
  <style>
    .logo {
      font-size: 20px;
      font-weight: bold;
    }
    .profile-dropdown {
      position: relative;
      cursor: pointer;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 120px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
      z-index: 1;
    }
    .dropdown-content a {
      color: black;
      padding: 10px;
      text-decoration: none;
      display: block;
    }
    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
    .dropdown-content.show {
      display: block;
    }
    .submit-btn {
      padding: 10px;
      margin-top: 10px;
      border: none;
      color: white;
      background-color: #007bff;
      cursor: pointer;
    }
    .submit-btn[disabled] {
      background-color: #ccc;
      cursor: not-allowed;
    }
    .success-message {
      color: green;
      margin-top: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>
<div class="topbar">
  <div class="logo">👤 E.A.M</div>
  <div class="profile-dropdown">
    <span onclick="toggleDropdown()"><?php echo htmlspecialchars($employeeName); ?> ⬇️</span>
    <div id="dropdown" class="dropdown-content">
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="container">
  <div class="sidebar">
    <ul>
      <li><a href="index.php">My Profile</a></li>
      <li class="active">Attendance Form</li>
      <li><a href="#">Lobby</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="card">
      <h2>Attendance Form</h2>
      <form id="attendanceForm" method="POST" action="">
        <label>Shift *</label>
        <select name="shift" id="shiftSelect" required <?php echo ($buttonsDisabled || ($checkedIn && !$checkedOut)) ? 'disabled' : ''; ?>>
          <option value="">Select Shift</option>
          <?php foreach ($shifts as $shift): ?>
            <option value="<?php echo $shift['id']; ?>" <?php echo ($shift['id'] == $selectedShiftId) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($shift['shift_name']) . " ({$shift['start_time']} - {$shift['end_time']})"; ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Message</label>
        <textarea name="message" rows="3"></textarea>

        <button type="button" id="checkinBtn" class="submit-btn" <?php echo ($buttonsDisabled || ($checkedIn && !$checkedOut)) ? 'disabled' : ''; ?>>🔁 CHECK IN</button>
        <button type="button" id="checkoutBtn" class="submit-btn" style="background-color: #28a745;" <?php echo ($buttonsDisabled || (!$checkedIn || $checkedOut)) ? 'disabled' : ''; ?>>✅ CHECK OUT</button>

        <?php if ($buttonsDisabled): ?>
          <p style="color: #555; margin-top: 10px;">You have already checked out today. Attendance actions will be enabled tomorrow.</p>
        <?php endif; ?>
      </form>
      <div id="successMessage" class="success-message hidden"></div>
    </div>
  </div>
</div>

<script>
function toggleDropdown() {
  document.getElementById("dropdown").classList.toggle("show");
}

window.onclick = function(e) {
  if (!e.target.closest('.profile-dropdown')) {
    const d = document.getElementById("dropdown");
    if (d.classList.contains("show")) d.classList.remove("show");
  }
}

const checkinBtn = document.getElementById("checkinBtn");
const checkoutBtn = document.getElementById("checkoutBtn");
const successMessage = document.getElementById("successMessage");
const shiftSelect = document.getElementById("shiftSelect");

checkinBtn.addEventListener("click", function () {
  const shiftId = shiftSelect.value;
  if (!shiftId) {
    alert('Please select a shift.');
    return;
  }

  fetch('attendance_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=checkin&shift_id=${shiftId}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      successMessage.textContent = data.message;
      successMessage.classList.remove('hidden');
      checkinBtn.disabled = true;
      checkinBtn.style.display = 'none';
      checkoutBtn.disabled = false;
      checkoutBtn.style.display = 'inline-block';
      shiftSelect.disabled = true;
    } else {
      alert(data.message);
    }
  });
});

checkoutBtn.addEventListener("click", function () {
  fetch('attendance_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=checkout'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      successMessage.textContent = data.message;
      successMessage.classList.remove('hidden');
      checkoutBtn.disabled = true;
      checkoutBtn.style.display = 'none';
      shiftSelect.disabled = false;
    } else {
      alert(data.message);
    }
  });
});
</script>
</body>
</html>
