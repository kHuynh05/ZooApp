<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get POST data
$report_id = $_POST['report_id'] ?? null;
$resolution_note = $_POST['resolution_note'] ?? null;

// Validate input
if (!$report_id || !$resolution_note) {
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify the report exists and is assigned to this caretaker
    $verify_sql = "SELECT assigned_id FROM reports WHERE report_id = ? AND status = 'ongoing'";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $report_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $report = $result->fetch_assoc();

    if (!$report) {
        throw new Exception('Report not found or already completed');
    }

    if ($report['assigned_id'] != $_SESSION['emp_id']) {
        throw new Exception('Not authorized to complete this report');
    }

    // Update the report status and add resolution note
    $update_sql = "UPDATE reports 
                   SET status = 'finished', 
                       resolution_note = ?
                   WHERE report_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $resolution_note, $report_id);

    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update report status');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Report completed successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
