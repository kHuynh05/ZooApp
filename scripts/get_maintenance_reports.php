<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

// Get all reports
$sql = "SELECT report_id, report_details, report_datetime, has_been_responded 
        FROM reports 
        ORDER BY report_datetime DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

header('Content-Type: application/json');
echo json_encode($reports);
