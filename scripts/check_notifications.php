<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

header('Content-Type: application/json');

// Check if user is logged in and has manager role
if (!in_array('view_reports', $allowed_actions)) {
    exit(json_encode([
        'success' => false,
        'error' => 'Unauthorized',
        'new_notifications' => false
    ]));
}

// Get notifications for unassigned reports
$sql = "SELECT mn.notif_id, mn.report_id, mn.suggested_id, mn.handled,
        r.enclosure_id, r.report_details,
        enc.enclosure_name,
        e.emp_name as suggested_name
        FROM manager_notifications mn
        JOIN reports r ON mn.report_id = r.report_id
        JOIN enclosures enc ON r.enclosure_id = enc.enclosure_id
        LEFT JOIN employees e ON mn.suggested_id = e.emp_id
        WHERE mn.handled = 0";

$result = $conn->query($sql);

if (!$result) {
    exit(json_encode([
        'success' => false,
        'error' => 'Database error',
        'new_notifications' => false
    ]));
}

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode([
    'success' => true,
    'new_notifications' => count($notifications) > 0,
    'notifications' => $notifications
]);
