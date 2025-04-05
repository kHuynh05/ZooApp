<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_animal') {
    $animal_name = $_POST['animal_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $date_of_rescue = $_POST['date_of_rescue'];
    $sex = $_POST['sex'];
    $status = $_POST['status'];
    $species_id = $_POST['species_id'];

    $sql = "INSERT INTO animals (animal_name, date_of_birth, date_of_rescue, sex, status, species_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $animal_name, $date_of_birth, $date_of_rescue, $sex, $status, $species_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Animal added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding animal: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
