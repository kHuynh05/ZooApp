<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$report_details = $_POST['report_details'] ?? null;
$enclosure_id = $_POST['enclosure_id'] ?? null;
$creator_id = $_POST['creator_id'] ?? null;

if (!$report_details || !$enclosure_id || !$creator_id) {
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Verify the caretaker has access to this enclosure
$verify_sql = "SELECT 1 FROM caretaker WHERE emp_id = ? AND enclosure_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $creator_id, $enclosure_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized: Not assigned to this enclosure']));
}

$sql = "INSERT INTO reports (enclosure_id, creator_id, report_details, status, report_datetime) 
        VALUES (?, ?, ?, 'ongoing', CURRENT_TIMESTAMP)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $enclosure_id, $creator_id, $report_details);

if ($stmt->execute()) {
    exit(json_encode(['success' => true]));
} else {
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}
