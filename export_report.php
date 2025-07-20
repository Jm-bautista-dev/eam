<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');

    // Set CSV headers
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=Shift_Report_$date.csv");
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open CSV output stream
    $output = fopen('php://output', 'w');

    // CSV header row
    fputcsv($output, ['Employee', 'Shift', 'Date', 'Time In', 'Time Out', 'Status', 'Tardiness', 'Work Duration']);

    // Get all employees and their attendance (if any) for the date
    $query = $conn->prepare("
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
        LEFT JOIN tbl_attendance a ON a.employee_id = e.id AND a.date = ?
        LEFT JOIN tbl_shift s ON s.id = a.shift_id
    ");
    $query->bind_param("s", $date);
    $query->execute();
    $result = $query->get_result();

    // Output each row
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['name'],
            $row['shift_name'] ?? '',
            $row['date'] ?? $date,
            $row['time_in'] ?? '',
            $row['time_out'] ?? '',
            $row['status'] ?? 'Absent',
            $row['tardiness'] ?? '',
            $row['work_duration'] ?? ''
        ]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Export Shift Report</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f9f9f9;
    }
    h2 {
      margin-bottom: 20px;
      color: #333;
    }
    form {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      display: inline-block;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
      margin-right: 10px;
      font-weight: 500;
    }
    input[type="date"], select {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-right: 15px;
      font-size: 14px;
    }
    button {
      padding: 8px 18px;
      background-color: #0052D4;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #003ea8;
    }
  </style>
</head>
<body>
  <h2>Export Shift Report</h2>
  <form method="POST" action="export_report.php">
    <label for="date">Select Date:</label>
    <input type="date" name="date" required>
    <button type="submit">Export CSV</button>
  </form>
</body>
</html>
