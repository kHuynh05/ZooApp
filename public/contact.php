<?php
include '../config/database.php';
include '../scripts/authorize.php';

// Function to check for empty inputs
function emptyInputContact($firstname, $lastname, $email, $title, $message) {
    return empty($firstname) || empty($lastname) || empty($email) || empty($title) || empty($message);
}

// Function to validate email
function invalidEmail($email) {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Handle form submission
if (isset($_POST["submit"])) {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $title = $_POST["title"];
    $message = $_POST["message"];

    // Error handlers
    if (emptyInputContact($firstname, $lastname, $email, $title, $message)) {
        $error = "emptyinput";
    }
    else if (invalidEmail($email)) {
        $error = "invalidemail";
    }
    else {
        // Insert into database
        $sql = "INSERT INTO contact (first_name, last_name, email, title, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            $error = "stmtfailed";
        }
        else {
            mysqli_stmt_bind_param($stmt, "sssss", $firstname, $lastname, $email, $title, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $error = "none";
        }
    }
    // Redirect to same page with error/success message
    header("Location: contact.php?error=" . $error);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
   <?php include('../includes/navbar.php'); ?>

    <div class="main-container">
        <div class="content-wrapper">
            <h1>Contact Us</h1>
            
            <?php
            if (isset($_GET["error"])) {
                switch($_GET["error"]) {
                    case "emptyinput":
                        echo "<p class='error-message'>Fill in all fields!</p>";
                        break;
                    case "invalidemail":
                        echo "<p class='error-message'>Choose a proper email!</p>";
                        break;
                    case "stmtfailed":
                        echo "<p class='error-message'>Something went wrong, try again!</p>";
                        break;
                    case "none":
                        echo "<p class='success-message'>Message sent successfully!</p>";
                        break;
                }
            }
            ?>
            
            <div class="contact-container">
                <div class="contact-form-section">
                    <form action="contact.php" method="POST" class="contact-form">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" id="firstname" name="firstname" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" name="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>

                <div class="contact-info-section">
                    <h2>Get in Touch</h2>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>6200 Hermann Park Drive, Houston, TX 77030</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <p>+1 234 567 8900</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <p>contact.zootopia@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <?php include('../includes/footer.php'); ?>
</body>
</html>