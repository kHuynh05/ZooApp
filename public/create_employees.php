<?php
include '../scripts/employeeRole.php';
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['emp_email'];
    $password = $_POST['emp_password'];
    $confirm_password = $_POST['confirmpassword'];
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['sex'];
    $ssn = $_POST['ssn'];
    $pay_rate = $_POST['pay_rate'];
    $start_date = $_POST['starting_day'];
    $role = $_POST['role'];

    $address_line1 = $_POST['address_line1'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    $query = "SELECT COUNT(*) FROM employees WHERE emp_email = ? OR ssn = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $ssn); // Bind the email parameter as a string
    $stmt->execute();
    $stmt->bind_result($email_count); // Get the count result
    $stmt->fetch();
    $stmt->close();

    // If the email exists in the database, alert the user
    if ($email_count > 0) {
        echo "<script>
        alert('This email/ssn is already associated with an existing account.');
        setTimeout(function() {
            location.href = 'create_employees.php';
        });
    </script>";
    } else {
        // If the email doesn't exist, proceed with the registration or other actions
        echo "Email is available for registration.";
    }

    // If they match, hash the password
    if ($password === $confirm_password) {
        $password = password_hash($password, PASSWORD_BCRYPT);

        // Insert into the employees table
        $sql = "INSERT INTO employees (
                    emp_name, emp_email, emp_password, date_of_birth, ssn, pay_rate, starting_day, sex, role 
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param(
                "sssssdsss",
                $name,
                $email,
                $password,
                $dob,
                $ssn,
                $pay_rate,
                $start_date,
                $gender,
                $role
            );
            if ($stmt->execute()) {
                $emp_id = $stmt->insert_id; // Get the inserted employee ID
                echo "Employee inserted successfully! Employee ID: " . $emp_id;
            } else {
                echo "Error inserting employee: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }

        // Insert into the emp_address table
        $address_sql = "INSERT INTO emp_address (emp_id, street, city_name, state, postal_code, country) 
                            VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($address_sql)) {
            $stmt->bind_param("isssss", $emp_id, $address_line1, $city, $state, $zip_code, $country);
            if ($stmt->execute()) {
                echo "Address inserted successfully.";
            } else {
                echo "Error inserting address: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing address statement: " . $conn->error;
        }
    } else {
        echo "Passwords do not match.";
    }
}
$conn->close();
?>

<div>
    <?php if ($message): ?>
        <p class="error"><?php echo $message; ?></p>
    <?php endif; ?>
    <h1>Create Employee</h1>
    <form id="form" method="POST">
        <div class="form-container">
            <div class="forms">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="emp_email">Email Address</label>
                    <input type="email" id="emp_email" name="emp_email" required>
                </div>

                <div class="form-group">
                    <label for="emp_password">Password</label>
                    <div class="password-input-group">
                        <input type="password" id="emp_password" name="emp_password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('emp_password')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmpassword">Confirm Password</label>
                    <div class="password-input-group">
                        <input type="password" id="confirmpassword" name="confirmpassword" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmpassword')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ssn">SSN</label>
                    <div class="password-input-group">
                        <input type="text" id="ssn" name="ssn" required>
                        <!-- No password toggle needed for SSN field -->
                    </div>
                </div>

                <div class="form-group">
                    <label for="pay_rate">Pay Rate</label>
                    <input type="number" id="pay_rate" name="pay_rate" required>
                </div>

                <div class="form-group">
                    <label for="starting_day">Starting Day</label>
                    <input type="date" id="starting_day" name="starting_day" required>
                </div>
            </div>
            <div class="forms">
                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="O">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address_line1">Address Line</label>
                    <input type="text" id="address_line1" name="address_line1" required>
                </div>
                <div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <select id="state" name="state" required>
                            <option value="">Select State</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="zip_code">Zip Code</label>
                        <input type="text" id="zip_code" name="zip_code" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="manager">Manager</option>
                        <option value="shop">Shopkeeper</option>
                        <option value="care">Caretaker</option>
                        <option value="vet">Veteranian</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit">Submit</button>
    </form>
</div>

<script>
    // Password visibility toggle function
    function togglePassword(id) {
        var passwordField = document.getElementById(id);
        if (passwordField.type === "password") {
            passwordField.type = "text";
        } else {
            passwordField.type = "password";
        }
    }

    function validateForm() {
        // Check if password and confirm password match
        var password = document.getElementById("emp_password").value;
        var confirmPassword = document.getElementById("confirmpassword").value;

        if (password !== confirmPassword) {
            // Show an alert to indicate the mismatch
            alert("Password and Confirm Password do not match.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
    document.getElementById("form").onsubmit = validateForm;
</script>