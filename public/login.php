<head>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<?php
// Include the necessary files for JWT (use Composer's autoload)
require_once '../vendor/autoload.php';
include '../config/database.php';

use Firebase\JWT\JWT;

// Initialize variables
$email = "";
$password = "";
$message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the posted form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check the email and get the hashed password
    $query = "SELECT member_id, password FROM members, customers WHERE customers.cust_id = members.member_id AND customers.cust_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $stored_password);
    $stmt->fetch();

    // Verify password
    if ($user_id && password_verify($password, $stored_password)) {

        // Create the payload for the JWT token
        $payload = [
            "iss" => "yourdomain.com", // Issuer
            "aud" => "yourdomain.com", // Audience
            "iat" => time(),           // Issued at
            "exp" => time() + 3600,    // Expiration time (1 hour)
            "sub" => $user_id          // Subject (user ID)
        ];

        // Encode the JWT token
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // Set JWT token as a cookie (HttpOnly, Secure)
        setcookie("jwt_token", $jwt, time() + 3600, "/", "", true, true); // 1 hour expiration

        // Redirect to dashboard
        header("Location: memberPortal.php");
        exit();
    } else {
        // Invalid credentials
        $message = "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
<div class="container">
    <?php include('../includes/navbar.php'); ?>
    <div class="login-container">
        <h2>Login</h2>

        <!-- Show error message if invalid credentials -->
        <?php if ($message): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="login.php">
            <div class="form-group">
                <input type="text" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-actions">
                <button type="submit">Login</button>
                <span class="register-link">New user? <a href="register.php">Register here</a></span>
            </div>
        </form>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>