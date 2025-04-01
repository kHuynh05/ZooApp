<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false]));
}

$report_id = $_POST['report_id'] ?? null;

if (!$report_id) {
    exit(json_encode(['success' => false]));
}

$sql = "UPDATE reports SET has_been_responded = 1 WHERE report_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);

exit(json_encode(['success' => $stmt->execute()]));
