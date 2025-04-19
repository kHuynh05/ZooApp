<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if (!in_array('view_reports', $allowed_actions)) {
    exit(json_encode(['error' => 'Unauthorized']));
}

$enclosure_id = $_GET['enclosure_id'] ?? null;

if (!$enclosure_id) {
    exit(json_encode(['error' => 'Enclosure ID is required']));
}

$sql = "SELECT 
    e.emp_id,
    e.emp_name,
    COUNT(CASE WHEN r.status = 'ongoing' THEN 1 END) as ongoing_reports
    FROM employees e
    JOIN caretaker c ON e.emp_id = c.emp_id
    LEFT JOIN reports r ON r.assigned_id = e.emp_id AND r.status = 'ongoing'
    WHERE e.role = 'care' 
    AND e.active = 1 
    AND c.enclosure_id = ?
    GROUP BY e.emp_id
    ORDER BY ongoing_reports ASC, e.emp_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $enclosure_id);
$stmt->execute();
$result = $stmt->get_result();

$caretakers = [];
while ($row = $result->fetch_assoc()) {
    $caretakers[] = $row;
}

echo json_encode($caretakers);
