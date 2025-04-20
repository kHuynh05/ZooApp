<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    include '../config/database.php';
    include '../scripts/employeeRole.php';

    if (!isset($_SESSION['emp_id'])) {
        throw new Exception('Not logged in');
    }

    // Get reports for the current caretaker
    $sql = "SELECT r.*, 
            e1.emp_name as creator_name,
            e2.emp_name as assigned_name,
            enc.enclosure_name
            FROM reports r
            LEFT JOIN employees e1 ON r.creator_id = e1.emp_id
            LEFT JOIN employees e2 ON r.assigned_id = e2.emp_id
            LEFT JOIN enclosures enc ON r.enclosure_id = enc.enclosure_id
            WHERE (r.creator_id = ? OR r.assigned_id = ?)
            ORDER BY r.report_datetime DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $_SESSION['emp_id'], $_SESSION['emp_id']);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Get result failed: " . $stmt->error);
    }

    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    // Even if there are no reports, return a successful response with an empty array
    echo json_encode([
        'success' => true,
        'reports' => $reports,
        'message' => count($reports) === 0 ? 'No reports found' : null
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'reports' => [],
        'message' => $e->getMessage()
    ]);
}
