<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if (!in_array('view_reports', $allowed_actions)) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$report_id = $_POST['report_id'] ?? null;
$assigned_id = $_POST['assigned_id'] ?? null;
$enclosure_id = $_POST['enclosure_id'] ?? null;

if (!$report_id || !$assigned_id || !$enclosure_id) {
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify caretaker belongs to the correct enclosure
    $verify_sql = "SELECT 1 FROM caretaker 
                   WHERE emp_id = ? AND enclosure_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $assigned_id, $enclosure_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        throw new Exception("Selected caretaker is not assigned to this enclosure");
    }

    // Update report assignment
    $update_sql = "UPDATE reports SET assigned_id = ? WHERE report_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $assigned_id, $report_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update report assignment");
    }

    // Mark notification as handled
    $notif_sql = "UPDATE manager_notifications 
                  SET handled = 1 
                  WHERE report_id = ?";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $report_id);
    $notif_stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
