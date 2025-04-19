<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

if (!in_array('view_reports', $allowed_actions)) {
    exit(json_encode(['error' => 'Unauthorized']));
}

$sql = "SELECT COUNT(*) as count 
        FROM manager_notifications 
        WHERE handled = 0";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode([
    'new_notifications' => $row['count'] > 0
]);
