<?php
include '../config/database.php';
include '../scripts/authorize.php';

$conn->begin_transaction();

try {
    // Get the JSON data
    $inputData = json_decode(file_get_contents("php://input"), true);
    // Check if profile data is present and prepare it for update
    $profileUpdateData = $inputData["profile"];
    $firstName = $profileUpdateData["first_name"] ?? null;
    $lastName = $profileUpdateData["last_name"] ?? null;
    $email = $profileUpdateData["email"] ?? null;
    $message = "";
    if (!empty($inputData["password"])) {
        $oldPassword = $inputData["password"]["oldPassword"];
        $newPassword = $inputData["password"]["newPassword"];

        // Check if old password matches the one in the database
        $stmt = $conn->prepare("SELECT password FROM members WHERE member_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($storedPassword);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($oldPassword, $storedPassword)) {
            throw new Exception("Old password is incorrect.");
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE members SET password = ? WHERE member_id = ?");
            $stmt->bind_param("si", $hashedPassword, $user_id);
            $stmt->execute();
            $stmt->close();
            $message ="password";
        }
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email.");
    }

    // Only update the profile fields if they were passed
    if ($firstName || $lastName || $email) {
        $stmt = $conn->prepare("UPDATE customers SET 
                                first_name = COALESCE(?, first_name), 
                                last_name = COALESCE(?, last_name), 
                                cust_email = COALESCE(?, cust_email) 
                                WHERE cust_id = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $user_id);
        $stmt->execute();
        $stmt->close();
        if($message == ""){
            $message = "personal info";
        }else{
            $message = "password and personal info";
        }
    }
    $conn->commit();
    echo json_encode(["success" => true, "error" => "Updated " . $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);

    exit();
}

$conn->close();
