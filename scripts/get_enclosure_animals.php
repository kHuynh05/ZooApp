<?php
include '../config/database.php';

if (!isset($_GET['enclosure_id'])) {
    echo json_encode(['error' => 'Enclosure ID not provided']);
    exit();
}

$enclosure_id = intval($_GET['enclosure_id']);

$sql = "SELECT a.animal_id, a.animal_name, s.species_name 
        FROM animals a 
        JOIN species s ON a.species_id = s.species_id 
        WHERE s.enclosure_id = ? AND a.deleted = 0 AND s.deleted = 0
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
