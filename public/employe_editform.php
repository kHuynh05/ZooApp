<?php
include '../config/database.php';

if (!isset($_SESSION['emp_id'])) {
    header("Location: employeeLogin.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Fetch current email
$stmt = $conn->prepare("SELECT emp_email FROM employees WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($emp_email);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .edit-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="password"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        #password-match-message {
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="edit-header">
        <h1>Edit my info</h1>
    </div>

    <div class="edit-container">

        <?php if ($message): ?>
            <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" id="registration-form" action="../scripts/edit_employee.php">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="emp_email" 
                       value="<?php echo htmlspecialchars($emp_email); ?>" required>
            </div>

            <div>
                <label for="password">New Password:</label>
                <input type="password" id="password" name="emp_password" required>
            </div>

            <div>
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <p id="password-match-message"></p>

            <input type="submit" value="Update Info">
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const message = document.getElementById('password-match-message');
        const form = document.getElementById('registration-form');

        function checkPasswordsMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password === '' || confirmPassword === '') {
                message.textContent = '';
                return;
            }

            if (password === confirmPassword) {
                message.textContent = 'Passwords match';
                message.style.color = 'green';
            } else {
                message.textContent = 'Passwords do not match';
                message.style.color = 'red';
            }
        }

        passwordInput.addEventListener('input', checkPasswordsMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordsMatch);

        form.addEventListener('submit', function(event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                alert("Passwords do not match!");
            }
        });
    </script>
</body>
</html>
