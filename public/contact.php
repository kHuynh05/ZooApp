<?php
include '../config/database.php';  // Ensure the correct path

if (isset($_POST['submit'])) {
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $subject = htmlspecialchars($_POST['title']);
    $mailfrom = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message']);

    // Validate email
    if (!filter_var($mailfrom, FILTER_VALIDATE_EMAIL)) {
        echo "<p>Invalid email format.</p>";
        exit;
    }

    $mailTo = "your-email@example.com";  // Replace with your actual email
    $headers = "From: " . $mailfrom . "\r\n";
    $headers .= "Reply-To: " . $mailfrom . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $stmt = $conn->prepare("INSERT INTO contact (first_name, last_name, email, title, message) VALUES (?, ?, ?, ?, ?)");

    // Check for errors in preparing the statement
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("sssss", $first_name, $last_name, $mailfrom, $subject, $message);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Record successfully inserted!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    $conn->close();



}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Contact Form</title>

    <head>
        <link rel="stylesheet" href="../assets/css/contact.css">
    </head>
</head>

<body>
    <main>
        <p>CONTACT US</p>
        <form class="contact-form" action="" method="post">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="message" placeholder="Message" required></textarea>
            <button type="submit" name="submit">SUBMIT</button>
        </form>
    </main>
</body>

</html>