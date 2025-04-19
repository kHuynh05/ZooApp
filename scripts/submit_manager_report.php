<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if (!in_array('view_reports', $allowed_actions)) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$creator_id = $_POST['creator_id'] ?? null;
$enclosure_id = $_POST['enclosure_id'] ?? null;
$report_details = $_POST['report_details'] ?? null;
$assigned_id = $_POST['assigned_id'] ?? null;

if (!$creator_id || !$enclosure_id || !$report_details || !$assigned_id) {
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify the creator is a manager
    $verify_sql = "SELECT role FROM employees WHERE emp_id = ? AND role = 'manager'";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $creator_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        throw new Exception("Unauthorized: Not a manager");
    }

    // Verify caretaker belongs to the correct enclosure
    $verify_caretaker_sql = "SELECT 1 FROM caretaker 
                            WHERE emp_id = ? AND enclosure_id = ?";
    $verify_caretaker_stmt = $conn->prepare($verify_caretaker_sql);
    $verify_caretaker_stmt->bind_param("ii", $assigned_id, $enclosure_id);
    $verify_caretaker_stmt->execute();
    $verify_caretaker_result = $verify_caretaker_stmt->get_result();

    if ($verify_caretaker_result->num_rows === 0) {
        throw new Exception("Selected caretaker is not assigned to this enclosure");
    }

    // Insert the report with assignment
    $sql = "INSERT INTO reports (enclosure_id, creator_id, assigned_id, report_details, status, report_datetime) 
            VALUES (?, ?, ?, ?, 'ongoing', CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $enclosure_id, $creator_id, $assigned_id, $report_details);

    if (!$stmt->execute()) {
        throw new Exception("Failed to create report");
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
