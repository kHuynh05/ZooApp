<?php
// Start the session and include necessary files
session_start();
include '../config/database.php';
include '../scripts/authorize.php';

// Check if the user is logged in and a member
if (!$is_member) {
    echo "<script>
        alert('Please log in as a member to access!');
        setTimeout(function() {
            location.href = 'login.php';
        }, 1000);
    </script>";
    exit();
}

// Get the user ID and the selected membership type
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session
$membership_type = $_POST['membership_type'];  // Get membership type from form submission

$query = "SELECT membership_end_date, reward_points FROM members WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // Bind the user_id parameter
$stmt->execute();
$stmt->bind_result($membership_end_date, $current_points);  // Bind both result variables
$stmt->fetch();  // Fetch the data

$stmt->close();
// Calculate the new end date (1 year from today)
$new_end_date = date('Y-m-d', strtotime('+1 year'));

// Get the discount based on the renewal status
$current_date = new DateTime(); // Existing membership end date from the form
$discount = ($current_date <= $membership_end_date) ? 0.25 : 0; // 25% discount for on-time renewal

// Define the prices for each membership type
$basePrices = array(
    "standard" => 70,
    "family" => 120,
    "vip" => 150
);

// Calculate the renewal amount
$amount = $basePrices[$membership_type] * (1 - $discount);
// Assuming you add 500 points for renewing the membership
$new_points = $current_points + 500;

// Update the membership details in the database
$query = "UPDATE members 
          SET membership_type = ?, membership_end_date = ?, membership_status = 'active', reward_points = $new_points
          WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $membership_type, $new_end_date, $user_id);

if ($stmt->execute()) {
    // Successfully updated the membership
    echo "<script>
        alert('Your membership has been renewed successfully!');
        location.href = '../public/memberPortal.php';  // Redirect to the member portal
    </script>";
} else {
    // Failed to update the membership
    echo "<script>
        alert('There was an error renewing your membership. Please try again later.');
    </script>";
}

$stmt->close();
?>
