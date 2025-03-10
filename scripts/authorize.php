<?php
 // Required to access session variables
include '../config/database.php';

// Check if user is logged in
$is_member = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $query = "SELECT * FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_member = $result->num_rows > 0;
    $stmt->close();
}
?>

