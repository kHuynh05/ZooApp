<?php // Start the session
include '../config/database.php';

// Initialize variables
$email = "";
$password = "";
$message = "";

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];

    // Remove the message from the session after displaying it
    unset($_SESSION['message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check email and get the hashed password
    $query = "SELECT emp_id, emp_password, role FROM employees WHERE emp_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($emp_id, $stored_password, $role);
    $stmt->fetch();


    // Verify password
    if ($emp_id && $password == $stored_password) {
        // Securely start the session for the logged-in user
        session_regenerate_id(true);
        $_SESSION['emp_id'] = $emp_id;
        $_SESSION['role'] = $role;
        // Redirect to member portal
        header("Location: employeePortal.php");
        exit();
    } else {
        $message = "Invalid password or email" . $emp_id;
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
        <h2>Employee Login</h2>

        <!-- Show error message if invalid credentials -->
        <?php if ($message): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="employeeLogin.php">
            <div class="form-group">
                <input type="text" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-actions">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>