<?php
// --- Ensure includes run only once ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/database.php';
include_once '../scripts/employeeRole.php'; // Keep for potential role checks if needed later

// --- Authentication Check (Essential!) ---
if (!isset($_SESSION['emp_id'])) {
    // If accessed directly without login OR via AJAX without session
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
        exit;
    } else {
        // Redirect non-AJAX requests if not logged in (optional, depends on how it's loaded)
        // header('Location: login.php'); // Or display an error message
        echo "<p class='message error'>Authentication required. Please log in.</p>";
        // Close DB here if necessary before exiting
        if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) $conn->close();
        exit;
    }
}

$emp_id = $_SESSION['emp_id'];
$message = ""; // For non-AJAX messages (primarily initial load errors)
$messageClass = "";
$current_email = ''; // Initialize
$current_empname = '';

// --- Handle AJAX POST Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    header('Content-Type: application/json'); // Set header for JSON response
    $response = ['status' => 'error', 'message' => 'An unexpected error occurred.']; // Default error response

    try {
        $new_email = trim($_POST['email'] ?? '');
        $new_empname = trim($_POST['emp_name'] ?? '');
        $new_password = $_POST['new_password'] ?? ''; // Use null coalescing
        $confirm_password = $_POST['confirm_password'] ?? '';

        $update_fields = [];
        $bind_params = [];
        $bind_types = "";
        $errors = [];

        // 1. Fetch current email (Needed for comparison)
        $stmt_fetch = $conn->prepare("SELECT emp_email, emp_name FROM employees WHERE emp_id = ?");
        if (!$stmt_fetch) throw new Exception("Database error preparing to fetch current data.");
        $stmt_fetch->bind_param("i", $emp_id);
        $stmt_fetch->execute();
        $stmt_fetch->bind_result($current_email_db, $current_empname_db);
        if (!$stmt_fetch->fetch()) throw new Exception("Could not find employee data.");
        $stmt_fetch->close();
        $current_email = $current_email_db; // Assign for logic below
        $current_empname = $current_empname_db;


        // 2. Validate Email
        if (!empty($new_email) && $new_email !== $current_email) {
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            } else {
                // Check uniqueness only if email is changing
                $stmt_check = $conn->prepare("SELECT emp_id FROM employees WHERE emp_email = ? AND emp_id != ?");
                if (!$stmt_check) throw new Exception("Database error checking email uniqueness.");
                $stmt_check->bind_param("si", $new_email, $emp_id);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $errors[] = "This email address is already in use.";
                } else {
                    $update_fields[] = "emp_email = ?";
                    $bind_params[] = $new_email;
                    $bind_types .= "s";
                }
                $stmt_check->close();
            }
        } elseif (empty($new_email)) {
            $new_email = $current_email;
             //$errors[] = "Email address cannot be empty."; // Add check for empty email
        }


        // 3. Validate Password
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) < 6) { // Example: Add minimum length validation
                     $errors[] = "New password must be at least 6 characters long.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $update_fields[] = "emp_password = ?";
                    $bind_params[] = $hashed_password;
                    $bind_types .= "s";
                }
            } else {
                $errors[] = "New passwords do not match.";
            }
        } elseif (!empty($confirm_password) && empty($new_password)) {
             // If confirm is filled but new is not
             $errors[] = "Please enter the new password if you wish to change it.";
        }

        //Validate employee name
        if (!empty($new_empname)) {
            if (strlen($new_empname) < 2) {
                $errors[] = "Name must be at least 2 characters.";
            } else {
                $update_fields[] = "emp_name = ?";
                $bind_params[] = $new_empname;
                $bind_types .= "s";
            }
        } else {
            $new_empname = $current_empname;
            //$errors[] = "Employee name cannot be empty.";
        }

        // 4. Check for Errors
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // 5. Perform Update (if there are fields to update)
        if (!empty($update_fields)) {
            $bind_params[] = $emp_id; // Add emp_id for the WHERE clause
            $bind_types .= "i";
            $sql = "UPDATE employees SET " . implode(', ', $update_fields) . " WHERE emp_id = ?";
            $stmt_update = $conn->prepare($sql);

            if (!$stmt_update) throw new Exception("Error preparing update statement.");

            // Use array spread operator '...' for bind_param
            $stmt_update->bind_param($bind_types, ...$bind_params);

            if ($stmt_update->execute()) {
                if ($stmt_update->affected_rows > 0) {
                    $response = ['status' => 'success', 'message' => 'Profile updated successfully!'];
                    // If email changed, include the new email in the response
                    if (in_array("emp_email = ?", $update_fields)) {
                         $response['new_email'] = $new_email;
                    }
                } else {
                     // Query succeeded but no rows were changed (potentially submitted same data)
                     $response = ['status' => 'info', 'message' => 'No changes detected or applied.'];
                }
            } else {
                // Database execution error during update
                error_log("Error updating profile for emp_id $emp_id: " . $stmt_update->error); // Log detailed error
                throw new Exception("An error occurred while updating your profile."); // Generic message
            }
            $stmt_update->close();
        } else {
            // No fields were marked for update (e.g., submitted empty form)
             $response = ['status' => 'info', 'message' => 'No changes submitted.'];
        }

    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
        http_response_code(400); // Bad Request or appropriate error code
    } finally {
        // Close DB connection if it's open
        if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
            $conn->close();
        }
        // Echo the JSON response and exit
        echo json_encode($response);
        exit;
    }
} else {
    // --- GET Request (Initial Page Load): Fetch current email for the form ---
    // Moved this fetch outside the POST block
    $stmt_fetch = $conn->prepare("SELECT emp_email, emp_name FROM employees WHERE emp_id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $emp_id);
        $stmt_fetch->execute();
        $stmt_fetch->bind_result($current_email_db, $current_empname_db);
        $stmt_fetch->fetch();
        $stmt_fetch->close();
        $current_email = $current_email_db; // Assign to variable used in form value
        $current_empname = $current_empname_db;
    } else {
        $message = "Error fetching current user data."; // Display error on page load if needed
        $messageClass = "error";
        // Log the detailed error
        error_log("Error fetching initial email for emp_id $emp_id: " . $conn->error);
    }
     // Close DB connection if still open after GET request processing
    if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
         $conn->close();
    }
}

// --- HTML Form Output ---
?>

<div class="form-container">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    

    <?php // Display initial load errors (e.g., failed to fetch email) ?>
    <?php if (!empty($message)) : ?>
        <div class="message <?php echo htmlspecialchars($messageClass); ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php // Placeholder for AJAX messages ?>
    <div class = "form-inner-wrapper">
        <h1>Edit Profile</h1>
        <div id="editProfileMessage" class="message" style="display: none;"></div>

        <form id="editProfileForm" method="POST" action="edit_employee.php"> <?php /* Action points to self, but JS will intercept */ ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email ?? ''); ?>">
            </div>

            <hr style="margin: 20px 0;">

            <div class="form-group">
                <label for="new_empname">New Name</label>
                <input type="text" id="emp_name" name="emp_name" value="<?php echo htmlspecialchars($current_empname ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-input-group">
                    <input type="password" id="new_password" name="new_password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password')"></i>
                </div>
                <small>Leave blank to keep current password. (Min. 6 characters)</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-input-group">
                    <input type="password" id="confirm_password" name="confirm_password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                </div>
                <small>Required if entering a new password.</small>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</div>

<?php /* JavaScript for AJAX submission and UI updates */ ?>
<script>
    // Ensure togglePassword function exists (ideally move to global JS)
    if (typeof togglePassword !== 'function') {
        function togglePassword(id) {
            var passwordField = document.getElementById(id);
            if (!passwordField) return;
            var icon = passwordField.nextElementSibling; // Assumes icon is directly after input
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon?.classList.remove('fa-eye');
                icon?.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                icon?.classList.remove('fa-eye-slash');
                icon?.classList.add('fa-eye');
            }
        }
    }


    const editForm = document.getElementById('editProfileForm');
    const messageDiv = document.getElementById('editProfileMessage');
    const emailInput = document.getElementById('email');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitButton = editForm.querySelector('button[type="submit"]');


    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            messageDiv.style.display = 'none'; // Hide previous messages
            messageDiv.className = 'message'; // Reset message class
            submitButton.disabled = true; // Disable button during submission

            // Client-side check (optional but good UX)
            if (newPasswordInput.value !== "" && newPasswordInput.value !== confirmPasswordInput.value) {
                showMessage("New passwords do not match.", "error");
                submitButton.disabled = false;
                return; // Stop submission
            }

            const formData = new FormData(editForm);

            fetch('edit_employee.php', { // Post to the same file
                method: 'POST',
                headers: { // Important for PHP to detect AJAX
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                 // Check if response is ok (status in the range 200-299)
                 // We also need the JSON body regardless to get the message
                 return response.json().then(data => ({ ok: response.ok, status: response.status, data }));
            })
            .then(({ ok, status, data }) => {
                if (data && data.message) {
                    if (data.status === 'success') {
                        showMessage(data.message, 'success');
                        // Clear password fields on success
                        newPasswordInput.value = '';
                        confirmPasswordInput.value = '';
                        // Update email field if it was changed and returned in response
                        if(data.new_email) {
                            emailInput.value = data.new_email;
                        }
                    } else if (data.status === 'info') {
                         showMessage(data.message, 'info');
                         // Optionally clear password fields even if no change
                         newPasswordInput.value = '';
                         confirmPasswordInput.value = '';
                    } else {
                        // Handle errors reported by the server (e.g., validation errors)
                        showMessage(data.message, 'error');
                    }
                } else {
                     // Handle unexpected response format or network errors surfaced as non-ok status
                     showMessage('An unexpected error occurred. Status: ' + status, 'error');
                     console.error('Unexpected response:', data);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showMessage('An error occurred while submitting the form. Please check your connection.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false; // Re-enable button
            });
        });
    }

    // Helper function to display messages
    function showMessage(message, type) {
        messageDiv.innerHTML = message; // Use innerHTML to render <br> tags from validation errors
        messageDiv.className = `message ${type}`; // Set class for styling (e.g., .success, .error, .info)
        messageDiv.style.display = 'block'; // Make message div visible
    }

</script>
<style>
    /* --- General Container Styling --- */

.form-inner-wrapper{
    display: flex;
    flex-direction: column;
    align-items: stretch;
    width: 100%;
}

#editProfileMessage{
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}

.form-container {
    max-width: 500px; /* Or adjust as needed */
    margin: 20px auto; /* Center the form */
    padding: 25px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: sans-serif; /* Or your preferred font */
}

.form-container h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    font-size: 1.8em;
    padding-left: 5px;
}

/* --- Form Group Styling --- */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
}

.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Include padding and border in element's total width/height */
    transition: border-color 0.2s ease-in-out;
}

.form-group input[type="email"]:focus,
.form-group input[type="password"]:focus {
    border-color: #007bff; /* Highlight on focus */
    outline: none; /* Remove default outline */
}

/* --- Password Input Specifics --- */
.password-input-group {
    position: relative; /* Needed for absolute positioning of the icon */
}

.password-input-group input[type="password"] {
    /* Ensure padding accommodates the icon */
    padding-right: 40px; /* Adjust as needed */
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #888;
    transition: color 0.2s ease-in-out;
}

.password-toggle:hover {
    color: #333;
}

/* --- Helper Text --- */
.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 0.85em;
    color: #777;
}

/* --- Horizontal Rule --- */
hr {
    border: none;
    height: 1px;
    background-color: #eee;
    margin: 25px 0; /* Adjust spacing */
}

/* --- Submit Button --- */
button[type="submit"] {
    display: block;
    width: 100%;
    padding: 12px 15px;
    background-color: seagreen; /* Example green */
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1em;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

button[type="submit"]:hover {
    background-color: green; /* Darker green on hover */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

button[type="submit"]:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}


/* --- Message Styling --- */
.message {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
    display: block; /* Make sure it takes up space even if initially hidden in JS */
}

.message.success {
    background-color: #d4edda; /* Light green */
    color: #155724; /* Dark green */
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da; /* Light red */
    color: #721c24; /* Dark red */
    border: 1px solid #f5c6cb;
}

.message.info {
    background-color: #d1ecf1; /* Light blue */
    color: #0c5460; /* Dark blue */
    border: 1px solid #bee5eb;
}

/* --- Responsive Adjustments (Optional) --- */
@media (max-width: 600px) {
    .form-container {
        margin: 10px;
        padding: 15px;
    }

    .form-container h1 {
        font-size: 1.5em;
    }
}
</style>