<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $_SESSION['emp_id'];
    $enclosure_id = $_POST['enclosure_id'];
    $animal_id = $_POST['animal_id'];

    // Verify caretaker is assigned to this enclosure
    $sql = "SELECT 1 FROM caretaker 
            WHERE emp_id = ? AND enclosure_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $emp_id, $enclosure_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Error: You are not assigned to this enclosure.";
        header("Location: employeePortal.php");
        exit();
    }

    // Verify animal belongs to the enclosure
    $sql = "SELECT 1 FROM animals 
            WHERE animal_id = ? AND enclosure_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $animal_id, $enclosure_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Error: Invalid animal selection.";
        header("Location: employeePortal.php");
        exit();
    }

    // Process the feeding record
    $food_type = $_POST['food_type'];
    $quantity = $_POST['quantity'];
    $feeding_time = $_POST['feeding_time'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO feeding_records (animal_id, food_type, kg_quantity, feeding_time, notes, emp_id) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdssi", $animal_id, $food_type, $quantity, $feeding_time, $notes, $emp_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Feeding record added successfully!";
    } else {
        $_SESSION['message'] = "Error adding feeding record.";
    }

    $stmt->close();
    header("Location: employeePortal.php");
    exit();
}
