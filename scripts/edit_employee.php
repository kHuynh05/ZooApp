<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['emp_id'])) {
    header("Location: ../public/employeeLogin.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_email = trim($_POST['emp_email']);
    $new_password = $_POST['emp_password'];

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Error: Invalid email format.";
    } elseif (empty($new_password)) {
        $message = "Error: Password cannot be empty.";
    } else {
        // Check if email already exists for another employee
        $check_stmt = $conn->prepare("SELECT emp_id FROM employees WHERE emp_email = ? AND emp_id != ?");
        $check_stmt->bind_param("si", $new_email, $emp_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "Error: This email is already in use by another employee.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_stmt = $conn->prepare("UPDATE employees SET emp_email = ?, emp_password = ? WHERE emp_id = ?");
            $update_stmt->bind_param("ssi", $new_email, $hashed_password, $emp_id);

            if ($update_stmt->execute()) {
                $message = "Information updated successfully!";
            } else {
                $message = "Error: Could not update information. Please try again.";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }

    $_SESSION['message'] = $message;
    header("Location: ../public/employe_editform.php");
    exit();
}

$conn->close();
?>