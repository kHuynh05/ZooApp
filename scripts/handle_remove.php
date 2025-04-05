<?php
include '../scripts/employeeRole.php';

// Check if user has permission to update animals
if (!in_array('update_animals', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'remove_species':
            $species_id = $_POST['species_id'];

            // Check if there are any animals of this species
            $check_sql = "SELECT COUNT(*) as count FROM animals WHERE species_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $species_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot remove species: There are still animals of this species in the zoo.']);
                exit();
            }

            $sql = "DELETE FROM species WHERE species_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $species_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Species removed successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error removing species: ' . $stmt->error]);
            }
            break;

        case 'remove_animal':
            $animal_id = $_POST['animal_id'];

            // First delete any conditions for this animal
            $sql = "DELETE FROM animal_conditions WHERE animal_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $animal_id);
            $stmt->execute();

            // Then delete the animal
            $sql = "DELETE FROM animals WHERE animal_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $animal_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Animal removed successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error removing animal: ' . $stmt->error]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
