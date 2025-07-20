<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

$date = $_POST['date'] ?? date('Y-m-d');

// Set headers for CSV download
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=Shift_Report_$date.csv");
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['Employee', 'Shift', 'Date', 'Time In', 'Time Out', 'Status', 'Tardiness', 'Work Duration']);

// Fetch data
$query = $conn->prepare("
  SELECT a.*, e.name, s.shift_name 
  FROM tbl_attendance a
  LEFT JOIN tbl_employee e ON a.employee_id = e.id
  LEFT JOIN tbl_shift s ON a.shift_id = s.id
  WHERE a.date = ?
");
$query->bind_param("s", $date);
$query->execute();
$result = $query->get_result();

// Write rows
while ($row = $result->fetch_assoc()) {
  fputcsv($output, [
    $row['name'],
    $row['shift_name'],
    $row['date'],
    $row['time_in'],
    $row['time_out'],
    $row['status'],
    $row['tardiness'],
    $row['work_duration']
  ]);
}

fclose($output);
exit;
