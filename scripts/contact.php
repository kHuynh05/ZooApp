<?php
// Include the database configuration file
include '../config/database.php';  // Or use require 'db_config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // SQL query to insert the data into the database
    $sql = "INSERT INTO contact_form (firstname, lastname, email, phone, message) 
            VALUES ('$firstname', '$lastname', '$email', '$phone', '$message')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Message sent successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
