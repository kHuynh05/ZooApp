<?php
// Include the database connection
include '../config/database.php'; // Or however you connect to your database

// Include the authorization file
include '../scripts/authorize.php';
echo($is_member);
// Check if user is a member and redirect accordingly
if ($is_member) {
    header("Location: memberPortal.php");
    exit();
}

// Continue with the regular ticket page content below
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tickets - Zoo Management</title>
    <!-- Your CSS and other head content -->
</head>
<body>
    <!-- Your regular ticket page content -->
    <h1>Purchase Tickets</h1>
    <!-- Rest of your ticket page -->
</body>
</html>
