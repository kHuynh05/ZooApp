<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get POST data
$report_id = $_POST['report_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;

// Validate input
if (!$report_id || !in_array($new_status, ['0', '1'])) {
    exit(json_encode(['success' => false, 'message' => 'Invalid input']));
}

// Update the report status
$sql = "UPDATE reports SET has_been_responded = ? WHERE report_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_status, $report_id);

if ($stmt->execute()) {
    exit(json_encode(['success' => true]));
} else {
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}
