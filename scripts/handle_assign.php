<?php
include '../scripts/employeeRole.php';
include '../config/database.php';

header('Content-Type: application/json');

// Check if user has permission to assign care
if (!in_array('assign_care', $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        // Handle removal
        $emp_id = $_POST['emp_id'];
        $enclosure_id = $_POST['enclosure_id'];

        // Delete the assignment
        $remove_sql = "DELETE FROM caretaker WHERE emp_id = ? AND enclosure_id = ?";
        $remove_stmt = $conn->prepare($remove_sql);
        $remove_stmt->bind_param("ii", $emp_id, $enclosure_id);

        if ($remove_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Caretaker assignment removed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing caretaker assignment: ' . $remove_stmt->error]);
        }
        $remove_stmt->close();
    } else {
        // Handle assignment
        $emp_id = $_POST['employee_id'];
        $enclosure_id = $_POST['enclosure_id'];

        // Verify employee has 'care' role
        $sql = "SELECT role FROM employees WHERE emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if ($employee && $employee['role'] == 'care') {
            // Check if assignment already exists
            $check_sql = "SELECT * FROM caretaker WHERE emp_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $emp_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing assignment
                $update_sql = "UPDATE caretaker SET enclosure_id = ? WHERE emp_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $enclosure_id, $emp_id);

                if ($update_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Caretaker assignment updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating caretaker assignment: ' . $update_stmt->error]);
                }
                $update_stmt->close();
            } else {
                // Insert new assignment
                $sql = "INSERT INTO caretaker (emp_id, enclosure_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $emp_id, $enclosure_id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Caretaker assigned successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error assigning caretaker: ' . $stmt->error]);
                }
            }
            $check_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Selected employee is not a caretaker.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
