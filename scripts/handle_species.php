<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_species') {
    $species_name = $_POST['species_name'];
    $class = $_POST['class'];
    $habitat = $_POST['habitat'];
    $diet_type = $_POST['diet_type'];
    $conservation_status = $_POST['conservation_status'];
    $description = $_POST['description'];
    $fun_fact = $_POST['fun_fact'];
    $enclosure_id = $_POST['enclosure_id'];

    // Handle image upload
    $img_path = null;
    if (isset($_FILES['species_image']) && $_FILES['species_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['species_image'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type - only allow jpg
        if ($file_extension !== 'jpg') {
            echo json_encode(['success' => false, 'message' => 'Error: Only JPG files are allowed.']);
            exit();
        }

        // Create standardized filename
        $standardized_name = strtolower(str_replace(' ', '-', trim($species_name))) . '-animal.jpg';
        $physical_path = '../assets/img/' . $standardized_name;
        $db_path = '../assets/img/' . $standardized_name;

        // Check if file already exists
        if (file_exists($physical_path)) {
            echo json_encode(['success' => false, 'message' => 'Error: An image for this species already exists.']);
            exit();
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $physical_path)) {
            $img_path = $db_path;  // Store the relative path in the database
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
            exit();
        }
    }

    $sql = "INSERT INTO species (species_name, class, habitat, diet_type, conservation_status, description, fun_fact, enclosure_id, img, count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $species_name, $class, $habitat, $diet_type, $conservation_status, $description, $fun_fact, $enclosure_id, $img_path);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Species added successfully!']);
    } else {
        // If database insert fails, remove the uploaded image
        if ($img_path && file_exists($physical_path)) {
            unlink($physical_path);
        }
        echo json_encode(['success' => false, 'message' => 'Error adding species: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
