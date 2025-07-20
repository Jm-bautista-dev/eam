<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // Check admin table
  $stmtAdmin = $conn->prepare("SELECT email FROM tbl_admin WHERE email = ? AND password = SHA2(?, 256)");
  $stmtAdmin->bind_param("ss", $email, $password);
  $stmtAdmin->execute();
  $resultAdmin = $stmtAdmin->get_result();

  if ($resultAdmin->num_rows > 0) {
    $_SESSION['admin'] = $email;
    header("Location: dashboard.php");
    exit();
  }

  // Check employee table
  $stmtEmp = $conn->prepare("SELECT id, password FROM tbl_employee WHERE email = ?");
  $stmtEmp->bind_param("s", $email);
  $stmtEmp->execute();
  $resultEmp = $stmtEmp->get_result();

  if ($row = $resultEmp->fetch_assoc()) {
    if ($password === $row['password']) {
      $_SESSION['employee_id'] = $row['id'];
      header("Location: index.php");
      exit();
    } else {
      $error = "❌ Incorrect password.";
    }
  } else {
    $error = "❌ No account found with that email.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Attendance System</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <div class="login-container">
    <h2>System Login</h2>
    <form method="POST">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="you@example.com" required>
      </div>

      <div class="form-group toggle-password">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="••••••••" required>
        <button type="button" class="toggle-password-btn" onclick="togglePassword()">Show</button>
      </div>

      <button type="submit" class="login-btn">Login</button>

      <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center; margin-top: 10px;"><?php echo $error; ?></p>
      <?php endif; ?>
    </form>
    <p class="footer-note">Use your email and password to log in.</p>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleBtn = document.querySelector('.toggle-password-btn');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = 'Hide';
      } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = 'Show';
      }
    }
  </script>
</body>
</html>
