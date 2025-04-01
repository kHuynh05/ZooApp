<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['emp_id']) || !isset($_GET['enclosure_id'])) {
    exit(json_encode([]));
}

$emp_id = $_SESSION['emp_id'];
$enclosure_id = $_GET['enclosure_id'];

// Verify this employee is assigned to this enclosure
$sql = "SELECT 1 FROM caretaker 
        WHERE emp_id = ? AND enclosure_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $emp_id, $enclosure_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit(json_encode([]));
}

// Get animals in this enclosure
$sql = "SELECT a.animal_id, a.animal_name, s.species_name 
        FROM animals a
        JOIN species s ON a.species_id = s.species_id
        WHERE a.enclosure_id = ?
        ORDER BY a.animal_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $enclosure_id);
$stmt->execute();
$result = $stmt->get_result();

$animals = [];
while ($row = $result->fetch_assoc()) {
    $animals[] = $row;
}

header('Content-Type: application/json');
echo json_encode($animals);
