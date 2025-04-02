<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false]));
}

$report_details = $_POST['report_details'] ?? null;

if (!$report_details) {
    exit(json_encode(['success' => false]));
}

$sql = "INSERT INTO reports (report_details, report_datetime, has_been_responded) 
        VALUES (?, CURRENT_TIMESTAMP, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $report_details);

exit(json_encode(['success' => $stmt->execute()]));
