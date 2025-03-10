<?php
// Check if the "jwt" cookie exists
if (isset($_COOKIE['jwt_token'])) {
    $jwt_token = $_COOKIE['jwt_token'];  // Retrieve the token
    echo "JWT Token: " . $jwt_token;  // You can use the token as needed
} else {
    echo "No JWT token found. Please log in.";
}
?>