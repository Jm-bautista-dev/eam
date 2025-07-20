<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employees - Attendance System</title>
  <link rel="stylesheet" href="employees.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <ul class="nav-links">
      <li><a href="Dashboard.php">Dashboard</a></li>
      <li class="active">Employees</li>
      <li><a href="Schedule.php">Schedule</a></li>
      <li><a href="ShiftReport.php">Shift Reports</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="header">
      <div><h3>EMPLOYEES</h3></div>
      <div class="profile">
        <img src="https://via.placeholder.com/40" alt="Admin" onclick="toggleDropdown()" />
        <div class="dropdown" id="profileDropdown">
          <a href="#">My Profile</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="employees-section">
        <div class="employees-header">
          <h2>Employees</h2>
          <button id="addEmployeeBtn">+ Add Employee</button>
        </div>
        <div class="employees-controls">
          <label>Show
            <select id="entriesSelect">
              <option>10</option><option>25</option><option>50</option><option>100</option>
            </select> entries
          </label>
          <input type="text" id="employeesSearch" placeholder="Search...">
        </div>
        <table id="employeesTable">
          <thead>
            <tr>
              <th>Name</th><th>Username</th><th>Email</th><th>Mobile</th>
              <th>Department</th><th>Time In</th><th>Time Out</th><th>Tardiness</th><th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        <div class="table-footer">
          <div id="paginationInfo"></div>
          <div id="paginationControls"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Employee Modal -->
  <div id="employeeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000080; z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px;">
      <h3>Add Employee</h3>
      <form id="employeeForm">
        <input type="text" name="name" placeholder="Full Name" required style="width:100%; margin-bottom:10px; padding:8px;">
        <input type="text" name="username" placeholder="Username" required style="width:100%; margin-bottom:10px; padding:8px;">
        <input type="email" name="email" placeholder="Email" style="width:100%; margin-bottom:10px; padding:8px;">
        <input type="text" name="mobile" placeholder="Mobile" style="width:100%; margin-bottom:10px; padding:8px;">
        <select name="department_id" required style="width:100%; margin-bottom:10px; padding:8px;">
          <option value="">Select Department</option>
          <?php
            $dept = $conn->query("SELECT id, department_name FROM tbl_department ORDER BY department_name ASC");
            while ($row = $dept->fetch_assoc()) {
              echo "<option value='{$row['id']}'>{$row['department_name']}</option>";
            }
          ?>
        </select>
        <select name="status" style="width:100%; margin-bottom:10px; padding:8px;">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
        <button type="submit" style="width:100%; background:#007bff; color:#fff; border:none; padding:10px;">Submit</button>
      </form>
      <button onclick="closeModal()" style="margin-top:10px;">Cancel</button>
    </div>
  </div>

  <!-- Edit Employee Modal -->
<div id="editEmployeeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000080; z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:8px; width:400px;">
    <h3>Edit Employee</h3>
    <form id="editEmployeeForm">
      <input type="hidden" name="id" id="edit_id">
      <input type="text" name="name" id="edit_name" placeholder="Full Name" required style="width:100%; margin-bottom:10px; padding:8px;">
      <input type="text" name="username" id="edit_username" placeholder="Username" required style="width:100%; margin-bottom:10px; padding:8px;">
      <input type="email" name="email" id="edit_email" placeholder="Email" style="width:100%; margin-bottom:10px; padding:8px;">
      <input type="text" name="mobile" id="edit_mobile" placeholder="Mobile" style="width:100%; margin-bottom:10px; padding:8px;">
      <select name="department_id" id="edit_department" required style="width:100%; margin-bottom:10px; padding:8px;">
        <option value="">Select Department</option>
        <?php
          $dept = $conn->query("SELECT id, department_name FROM tbl_department ORDER BY department_name ASC");
          while ($row = $dept->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['department_name']}</option>";
          }
        ?>
      </select>
      <select name="status" id="edit_status" style="width:100%; margin-bottom:10px; padding:8px;">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
      <button type="submit" style="width:100%; background:#28a745; color:#fff; border:none; padding:10px;">Update</button>
    </form>
    <button type="button" onclick="deleteEmployee()" style="width:100%; background:#dc3545; color:#fff; border:none; padding:10px; margin-top:10px;">Delete</button>

    <button onclick="closeEditModal()" style="margin-top:10px;">Cancel</button>
  </div>
</div>

  

  <!-- JavaScript -->
  <script>
  function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  }

  window.addEventListener('click', function (e) {
    const profile = document.querySelector('.profile');
    const dropdown = document.getElementById('profileDropdown');
    if (!profile.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  document.getElementById("addEmployeeBtn").addEventListener("click", function() {
    document.getElementById("employeeModal").style.display = "flex";
  });

  function closeModal() {
    document.getElementById("employeeModal").style.display = "none";
  }

  document.getElementById("employeeForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("add_employee.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(data => {
      alert(data);
      this.reset();
      closeModal();
      loadEmployees();
    });
  });

  function loadEmployees() {
    fetch("get_employees.php")
      .then(res => res.text())
      .then(html => {
        document.querySelector("#employeesTable tbody").innerHTML = html;
      });
  }

  function editEmployee(id) {
    alert("Edit functionality for employee ID: " + id);
    // You can replace this with modal logic or redirect to edit_employee.php?id=id
  }

  loadEmployees(); // Initial load
  setInterval(loadEmployees, 10000); // Auto-refresh every 10 seconds

  function editEmployee(id) {
  fetch("get_employee.php?id=" + id)
    .then(res => res.json())
    .then(data => {
      document.getElementById("edit_id").value = data.id;
      document.getElementById("edit_name").value = data.name;
      document.getElementById("edit_username").value = data.username;
      document.getElementById("edit_email").value = data.email;
      document.getElementById("edit_mobile").value = data.mobile;
      document.getElementById("edit_department").value = data.department_id;
      document.getElementById("edit_status").value = data.status;
      document.getElementById("editEmployeeModal").style.display = "flex";
    });
}

function closeEditModal() {
  document.getElementById("editEmployeeModal").style.display = "none";
}

document.getElementById("editEmployeeForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch("update_employee.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
    closeEditModal();
    loadEmployees();
  });
});

function deleteEmployee() {
  const id = document.getElementById("edit_id").value;
  if (confirm("Are you sure you want to delete this employee?")) {
    fetch("delete_employee.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(id)
    })
    .then(res => res.text())
    .then(data => {
      alert(data);
      closeEditModal();
      loadEmployees();
    });
  }
}


</script>


</body>
</html>
