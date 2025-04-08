<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false]));
}

// Get POST data
$enclosure_id = $_POST['enclosure_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;

if (!$enclosure_id || !$new_status || !in_array($new_status, ['open', 'closed'])) {
    exit(json_encode(['success' => false]));
}

// Start transaction
$conn->begin_transaction();

try {
    // First verify the employee has access to this enclosure
    $sql = "SELECT e.enclosure_name 
            FROM enclosures e 
            JOIN caretaker c ON e.enclosure_id = c.enclosure_id 
            WHERE c.emp_id = ? AND e.enclosure_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['emp_id'], $enclosure_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Unauthorized");
    }

    $enclosure_name = $result->fetch_assoc()['enclosure_name'];

    // Update the enclosure status
    $sql = "UPDATE enclosures SET status = ? WHERE enclosure_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $enclosure_id);

    if (!$stmt->execute()) {
        throw new Exception("Status update failed");
    }

    // Create a report for this status change
    $report_details = "Enclosure '" . $enclosure_name . "' status changed to " . strtoupper($new_status);
    $sql = "INSERT INTO reports (report_details, report_datetime) 
            VALUES (?, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $report_details);

    if (!$stmt->execute()) {
        throw new Exception("Report creation failed");
    }

    // If we got here, everything worked
    $conn->commit();
    exit(json_encode(['success' => true]));
} catch (Exception $e) {
    // If anything failed, rollback changes
    $conn->rollback();
    exit(json_encode(['success' => false]));
}
