<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');
$dateToday = date('Y-m-d');

$result = $conn->query("
  SELECT e.id, e.name, e.username, e.email, e.mobile, d.department_name,
         a.time_in, a.time_out, a.tardiness
  FROM tbl_employee e
  LEFT JOIN tbl_department d ON e.department_id = d.id
  LEFT JOIN (
    SELECT employee_id, time_in, time_out, tardiness
    FROM tbl_attendance
    WHERE date = '$dateToday'
  ) a ON e.id = a.employee_id
");

while ($row = $result->fetch_assoc()) {
  $timeIn = $row['time_in'] ?? '—';
  $timeOut = $row['time_out'] ?? '—';
  $tardiness = $row['tardiness'] ?? '—';

  echo "<tr>
    <td>{$row['name']}</td>
    <td>{$row['username']}</td>
    <td>{$row['email']}</td>
    <td>{$row['mobile']}</td>
    <td>{$row['department_name']}</td>
    <td>{$timeIn}</td>
    <td>{$timeOut}</td>
    <td>{$tardiness}</td>
    <td><button onclick=\"editEmployee({$row['id']})\">Edit</button></td>
  </tr>";
}
?>
