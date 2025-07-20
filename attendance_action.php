<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "db_connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$employeeId = $_SESSION['employee_id'];
$action = $_POST['action'] ?? '';
$currentDateTime = date('Y-m-d H:i:s');
$dateToday = date('Y-m-d');

// ✅ CHECK-IN
if ($action === 'checkin') {
    $shiftId = $_POST['shift_id'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!$shiftId) {
        echo json_encode(['success' => false, 'message' => 'Shift ID is required']);
        exit();
    }

    // Check if already checked in
    $stmt = $conn->prepare("SELECT id FROM tbl_attendance WHERE employee_id = ? AND date = ?");
    $stmt->bind_param("is", $employeeId, $dateToday);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You already checked in today.']);
        exit();
    }

    // Fetch shift time
    $shiftQuery = $conn->prepare("SELECT start_time, end_time FROM tbl_shift WHERE id = ?");
    $shiftQuery->bind_param("i", $shiftId);
    $shiftQuery->execute();
    $shiftResult = $shiftQuery->get_result();
    $shiftData = $shiftResult->fetch_assoc();

    if (!$shiftData) {
        echo json_encode(['success' => false, 'message' => 'Shift not found.']);
        exit();
    }

    $startTime = $shiftData['start_time'];
    $endTime = $shiftData['end_time'];

    $currentDateTimeObj = new DateTime($currentDateTime);
    $currentHour = (int)$currentDateTimeObj->format('H');

    // 🌓 Night shift logic
    if ($startTime > $endTime && $currentHour < 12) {
        $shiftDate = (new DateTime($dateToday))->modify('-1 day')->format('Y-m-d');
    } else {
        $shiftDate = $dateToday;
    }

    $shiftStartDateTime = new DateTime("$shiftDate $startTime");
    $checkInDateTime = new DateTime($currentDateTime);
    $status = ($checkInDateTime > $shiftStartDateTime) ? "Late" : "Ontime";
    $tardiness = ($status === "Late") ? $shiftStartDateTime->diff($checkInDateTime)->format('%H:%I:%S') : '00:00:00';

    // Insert attendance
    $insert = $conn->prepare("INSERT INTO tbl_attendance (employee_id, shift_id, date, time_in, status, tardiness, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$insert) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    $insert->bind_param("iisssss", $employeeId, $shiftId, $dateToday, $currentDateTime, $status, $tardiness, $message);
    if ($insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Check-in successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Check-in failed: ' . $insert->error]);
    }
    exit();
}

// ✅ CHECK-OUT
if ($action === 'checkout') {
    $stmt = $conn->prepare("SELECT id, time_in FROM tbl_attendance WHERE employee_id = ? AND date = ?");
    $stmt->bind_param("is", $employeeId, $dateToday);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_assoc();

    if (!$attendance) {
        echo json_encode(['success' => false, 'message' => 'No check-in record found.']);
        exit();
    }

    $attendanceId = $attendance['id'];
    $timeIn = new DateTime($attendance['time_in']);
    $timeOut = new DateTime($currentDateTime);
    $workDuration = $timeIn->diff($timeOut)->format('%H:%I:%S');

    $update = $conn->prepare("UPDATE tbl_attendance SET time_out = ?, work_duration = ? WHERE id = ?");
    if (!$update) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    $update->bind_param("ssi", $currentDateTime, $workDuration, $attendanceId);
    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Check-out successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Check-out failed: ' . $update->error]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit();
