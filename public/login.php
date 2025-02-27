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
    $query = "SELECT member_id, password FROM members WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $stored_password);
    $stmt->fetch();

    // Verify password
    if ($user_id && $password === $stored_password) {

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
        setcookie("jwt", $jwt, time() + 3600, "/", "", true, true); // 1 hour expiration

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

<h2>Login</h2>

<!-- Show error message if invalid credentials -->
<?php if ($message): ?>
    <p class="error"><?php echo $message; ?></p>
<?php endif; ?>

<!-- Login form -->
<form method="POST" action="login.php">
    <input type="text" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>