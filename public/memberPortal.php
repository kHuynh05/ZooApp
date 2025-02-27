<?php
// Check if the "jwt" cookie exists
if (isset($_COOKIE['jwt'])) {
    $jwt_token = $_COOKIE['jwt'];  // Retrieve the token
    echo "JWT Token: " . $jwt_token;  // You can use the token as needed
} else {
    echo "No JWT token found. Please log in.";
}
?>