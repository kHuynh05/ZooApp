<?php // Start the session
include '../config/database.php';
include '../scripts/authorize.php';

// Initialize variables
$email = "";
$password = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check email and get the hashed password
    $query = "SELECT member_id, password FROM members, customers WHERE customers.cust_id = members.member_id AND customers.cust_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $stored_password);
    $stmt->fetch();

    
    // Verify password
    if ($user_id && password_verify($password, $stored_password)) {
        // Securely start the session for the logged-in user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;

        // Redirect to member portal
        header("Location: homepage.php");
        exit();
    } else {
        $message = "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
<head>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
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