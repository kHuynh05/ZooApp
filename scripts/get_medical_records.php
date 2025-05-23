<?php
include '../config/database.php';

// Initialize the base query
$query = "SELECT ac.condition_id, ac.animal_id, ac.emp_id, ac.weight, ac.mood, 
                 ac.health_status, ac.additional_notes, ac.recorded_at,
                 a.animal_name, 
                 s.species_name,
                 e.emp_name,
                 enc.enclosure_name
          FROM animal_conditions ac
          JOIN animals a ON ac.animal_id = a.animal_id
          JOIN species s ON a.species_id = s.species_id
          JOIN employees e ON ac.emp_id = e.emp_id
          JOIN enclosures enc ON s.enclosure_id = enc.enclosure_id
          WHERE a.deleted = 0 AND s.deleted = 0";

$params = [];
$types = "";

// Handle animal filter
if (!empty($_POST['animal_id'])) {
    $query .= " AND ac.animal_id = ?";
    $params[] = $_POST['animal_id'];
    $types .= "i";
}

// Handle veterinarian filter
if (!empty($_POST['vet_id'])) {
    $query .= " AND ac.emp_id = ?";
    $params[] = $_POST['vet_id'];
    $types .= "i";
}

// Handle date range filter
if (!empty($_POST['startDate'])) {
    $query .= " AND DATE(ac.recorded_at) >= ?";
    $params[] = $_POST['startDate'];
    $types .= "s";
}

if (!empty($_POST['endDate'])) {
    $query .= " AND DATE(ac.recorded_at) <= ?";
    $params[] = $_POST['endDate'];
    $types .= "s";
}

$query .= " ORDER BY ac.recorded_at DESC LIMIT 50";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Process results
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['records' => $records]);
