<?php
// Start the session and include necessary files
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

// Ensure that membership_type is set in session before processing
if (isset($_POST['membership_type'])) {
    $membership_type = $_POST['membership_type'];  // Get the selected membership type from the form
    $_SESSION['membership_type'] = $membership_type;  // Store it in the session
} else {
    echo "<script>
        alert('No membership type selected!');
        setTimeout(function() {
            location.href = 'member_portal.php';  // Redirect back to the portal if no membership selected
        }, 1000);
    </script>";
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session

// Fetch current membership details from the database
$query = "SELECT membership_end_date, reward_points FROM members WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // Bind the user_id parameter
$stmt->execute();
$stmt->bind_result($membership_end_date, $current_points);  // Bind the result variables
$stmt->fetch();  // Fetch the data

if (!$membership_end_date) {
    echo "<script>
        alert('No membership found for this user.');
        setTimeout(function() {
            location.href = 'member_portal.php';  // Redirect back to the portal
        }, 1000);
    </script>";
    exit();
}

$stmt->close();

// Calculate the new end date (1 year from today)
$new_end_date = date('Y-m-d', strtotime('+1 year'));

// Check for discount based on the renewal status (on time renewal gets a discount)
$current_date = new DateTime();  // Current date
$discount = ($current_date <= new DateTime($membership_end_date)) ? 0.25 : 0;  // Apply 25% discount if renewing on time

// Define the base prices for each membership type
$basePrices = array(
    "Standard" => 70,
    "Premium" => 120,
    "Vip" => 150
);

// Calculate the renewal amount with discount applied
$amount = $basePrices[$membership_type] * (1 - $discount);

// Add 500 reward points for renewing the membership
$new_points = $current_points + 500;

// Update the membership details in the database
$query = "UPDATE members 
          SET membership_type = ?, membership_end_date = ?, membership_status = 'active', reward_points = ?
          WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $membership_type, $new_end_date, $new_points, $user_id);
$stmt->execute();
$stmt->close();

$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$type = "registration";
$query = "INSERT INTO transactions (transaction_date, transaction_time, cust_id, total_profit, transaction_type) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssdis", $current_date, $current_time, $user_id, $amount, $type);

if ($stmt->execute()) {
    // Successfully updated the membership
    echo "<script>
        alert('Your membership has been renewed successfully!');
        setTimeout(function() {
            location.href = '../public/memberPortal.php';  // Redirect to the member portal
        }, 1000);
    </script>";
} else {
    // Failed to update the membership
    echo "<script>
        alert('There was an error renewing your membership. Please try again later.');
        setTimeout(function() {
            location.href = '../public/memberPortal.php';  // Redirect back to the portal if error occurs
        }, 1000);
    </script>";
}

$stmt->close();
?>
