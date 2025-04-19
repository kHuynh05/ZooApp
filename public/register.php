<?php
// Include database connection
include '../config/database.php';
include '../scripts/authorize.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['cust_email'];
    $password = $_POST['cust_password'];
    $confirm_password = $_POST['confirm_password'];
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['sex'];
    $membership_type = $_POST['membership_type'];

    // Address data
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    // Check if password and confirm password match
    $query = "SELECT COUNT(*) FROM customers JOIN members ON customers.cust_id = members.member_id WHERE customers.cust_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email); // Bind the email parameter as a string
    $stmt->execute();
    $stmt->bind_result($email_count); // Get the count result
    $stmt->fetch();
    $stmt->close();

    // If the email exists in the database, alert the user
    if ($email_count > 0) {
        echo "<script>
        alert('This email is already associated with an existing membership or account.');
        setTimeout(function() {
            location.href = 'register.php';
        });
    </script>";
    } else {
        // If the email doesn't exist, proceed with the registration or other actions
        echo "Email is available for registration.";
    }
    if ($password !== $confirm_password) {
        echo "<script>alert('Password and confirm password do not match. Please try again.');</script>";
    } else {
        // If they match, hash the password
        $password = password_hash($password, PASSWORD_BCRYPT);

        // Set member start date if membership is selected
        $membership_start_date = date("Y-m-d");
        $membership_end_date = date("Y-m-d", strtotime("+1 year"));

        // Insert query for customer data
        $query = "SELECT cust_id FROM customers WHERE customers.cust_email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email); // Bind the email parameter as a string
        $stmt->execute();
        $stmt->bind_result($cust_id); // Get the count result
        $stmt->fetch();
        $stmt->close();

        if ($cust_id == null) {
            $sql_customer = "INSERT INTO customers (
                cust_email, date_of_birth, sex, first_name, last_name
            ) VALUES (?, ?, ?, ?, ?)";

            if ($stmt_customer = $conn->prepare($sql_customer)) {
                // Bind parameters for customer table
                $stmt_customer->bind_param(
                    "sssss",
                    $email,
                    $dob,
                    $gender,
                    $first_name,
                    $last_name
                );
            }
            if ($stmt_customer->execute()) {
                $cust_id = $stmt_customer->insert_id;
            }
        }

        // Execute query for customer
        // Get the last inserted cust_id
        // Insert query for member data
        $sql_member = "INSERT INTO members (member_id,
                    password, 
                    membership_start_date, membership_end_date, 
                    membership_type, membership_status, reward_points
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";


        if ($stmt_member = $conn->prepare($sql_member)) {
            // Bind parameters for member table
            $membership_status = 'active';
            // Set reward points to 0 for free membership, 500 for paid memberships
            $reward_points = ($membership_type === 'Free') ? 0 : 500;
            $stmt_member->bind_param(
                "isssssi",
                $cust_id,
                $password,
                $membership_start_date,
                $membership_end_date,
                $membership_type,
                $membership_status,
                $reward_points
            );

            // Execute query for member
            if ($stmt_member->execute()) {
                // Get the membership type and corresponding price
                $membership_type = $_POST['membership_type'];
                $basePrices = array(
                    "Free" => 0,
                    "Standard" => 70,
                    "Premium" => 120,
                    "Vip" => 150
                );
                $amount = $basePrices[$membership_type];

                // Insert transaction record
                $current_date = date('Y-m-d');
                $current_time = date('H:i:s');
                $type = "registration";
                $sql_transaction = "INSERT INTO transactions (transaction_date, transaction_time, cust_id, total_profit, transaction_type) VALUES (?, ?, ?, ?, ?)";

                if ($stmt_transaction = $conn->prepare($sql_transaction)) {
                    $stmt_transaction->bind_param("ssids", $current_date, $current_time, $cust_id, $amount, $type);
                    $stmt_transaction->execute();
                    $stmt_transaction->close();
                }

                // Insert query for customer address
                $sql_address = "INSERT INTO cust_address (
                            cust_id, street, city_name, state, postal_code, country
                        ) VALUES (?, ?, ?, ?, ?, ?)";

                if ($stmt_address = $conn->prepare($sql_address)) {
                    // Bind parameters for address table
                    $stmt_address->bind_param(
                        "isssss",
                        $cust_id,
                        $address_line1,
                        $city,
                        $state,
                        $zip_code,
                        $country
                    );

                    // Execute query for address
                    if ($stmt_address->execute()) {
                        echo "<script>alert('Registration and address saved successfully!'); window.location.href = 'login.php';</script>";
                    } else {
                        echo "<script>alert('Error saving address: " . $stmt_address->error . "');</script>";
                    }

                    $stmt_address->close();
                } else {
                    echo "<script>alert('Error preparing address query: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Error saving member data: " . $stmt_member->error . "');</script>";
            }

            $stmt_member->close();
        }
    }

    $stmt_customer->close();
}
$conn->close();
?>

<head>
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<?php include('../includes/navbar.php'); ?>
<div class="container">
    <!-- Left Sidebar for Tabs -->
    <h1>REGISTER FOR ZOO MEMBERSHIP</h1>
    <div class="tabs">
        <div class="tab active" onclick="showTab(1)">Step 1: Basic Info</div>
        <div class="tab" onclick="showTab(2)">Step 2: Membership</div>
        <div class="tab" onclick="showTab(3)">Step 3: Review</div>
    </div>

    <!-- Right Content Area -->
    <form class="form-container" id="registration-form" method="POST">
        <div id="error-message" class="error-message" style="display: none;"></div>
        <!-- Step 1: Basic Info -->
        <div class="tab-content active" id="step1">
            <h2>Step 1: Basic Information</h2>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="address_line1">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" required>
            </div>
            <div class="form-group">
                <label for="address_line2">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2">
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
                <label for="dob">Date of Birth</label>

                <input type="date" id="dob" name="date_of_birth"
                    max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="sex" required>
                    <option value="">Select gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="cust_email" required>
            </div>



            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="cust_password" required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmpassword">Confirm Password</label>
                <div class="password-input-group">
                    <input type="password" id="confirmpassword" name="confirm_password" required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmpassword')"></i>
                </div>
            </div>

            <!-- Address Section -->
        </div>

        <!-- Step 2: Membership -->
        <div class="tab-content" id="step2">
            <h2>Step 2: Choose Membership</h2>
            <div class="form-group">
                <label for="membership">Choose Membership Type</label>
                <select id="membership" name="membership_type" onchange="updateMembershipInfo()" required>
                    <option value="Standard">Standard</option>
                    <option value="Premium">Premium</option>
                    <option value="Vip">VIP</option>
                    <option value="Free">Free</option>
                </select>
            </div>
            <div id="membershipInfo">
                <h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a 15% discounted price.</h3>
                <div class='renew-img'><img class="renew-ticket" src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>
            </div>
            <div class="price-display">
                <p><strong>Base Price:</strong> <span id="base-price">$70.00</span></p>
            </div>
        </div>

        <!-- Step 3: Review -->
        <div class="tab-content" id="step3">
            <h2>Step 3: Review and Payment</h2>
            <div class="payment-summary">
                <h3>Registration Summary:</h3>
                <div class="membership-details">
                    <p><strong>Membership Type:</strong> <span id="selected-type">Free</span></p>
                    <p><strong>Base Price:</strong> <span id="total-base-price">$0.00</span></p>
                    <p><strong>Initial Registration Bonus:</strong> <span id="registration-bonus">0 points</span></p>
                    <p><strong>Total Cost:</strong> <span id="total-cost">$0.00</span></p>
                </div>
            </div>
            <button type="submit">Complete Registration</button>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
<script>
    function changeContent() {
        var select = document.getElementById("membership");
        var div = document.getElementById("membershipInfo");
        var selectedOption = select.value;

        if (selectedOption === "Standard") {
            div.innerHTML = "<h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a 15% discounted price. Applies to one person</h3> <div class='renew-img'><img class = 'renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>";
        } else if (selectedOption === "Premium") {
            div.innerHTML = "<h3>Perfect for families! This membership includes a 25% discount, offering a cost-effective way to enjoy the zoo together.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        } else if (selectedOption === "Vip") {
            div.innerHTML = "<h3>Experience the zoo like never before! VIP members get a 25% discount, access to exclusive events, behind-the-scenes tours, and discounts on tickets, food, and gift shop purchases. </h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        }
    }

    const countries = [{
            code: "USA",
            name: "United States"
        },
        {
            code: "CAN",
            name: "Canada"
        },
        {
            code: "GBR",
            name: "United Kingdom"
        },
        {
            code: "AUS",
            name: "Australia"
        },
        // Add more countries here
    ];

    // Get the select element
    const selectCountry = document.getElementById('country');

    // Loop through the array and add each country as an option
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.code;
        option.textContent = country.name;
        selectCountry.appendChild(option);
    });

    const states = [{
            code: "AL",
            name: "Alabama"
        },
        {
            code: "AK",
            name: "Alaska"
        },
        {
            code: "CA",
            name: "California"
        },
        // Add more states here
    ];

    // Get the select element for states
    const selectState = document.getElementById('state');

    // Loop through the array and add each state as an option
    states.forEach(state => {
        const option = document.createElement('option');
        option.value = state.code;
        option.textContent = state.name;
        selectState.appendChild(option);
    });

    function showError(message) {
        const errorMessageDiv = document.getElementById("error-message");
        errorMessageDiv.innerText = message;
        errorMessageDiv.style.display = "block";
        errorMessageDiv.style.backgroundColor = "#fee";
        errorMessageDiv.style.color = "red";
        errorMessageDiv.style.padding = "10px";
        errorMessageDiv.style.marginBottom = "15px";
        errorMessageDiv.style.borderRadius = "4px";
        errorMessageDiv.style.border = "1px solid #fcc";
    }

    function hideError() {
        const errorMessageDiv = document.getElementById("error-message");
        errorMessageDiv.style.display = "none";
    }

    function validateForm() {
        // Get all required inputs
        var allInputs = document.querySelectorAll('input[required], select[required]');
        var isValid = true;
        var emptyFields = [];

        // Check each required field
        for (var i = 0; i < allInputs.length; i++) {
            if (allInputs[i].value.trim() === "") {
                isValid = false;
                // Get the label text for the empty field
                var label = allInputs[i].previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    emptyFields.push(label.textContent.trim());
                }
            }
        }

        // Check password match
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirmpassword").value;
        if (password !== confirmPassword) {
            showError("Password and Confirm Password do not match.");
            return false;
        }

        // If form is not valid, show error message with empty fields
        if (!isValid) {
            var message = "Please fill in the following required fields:\n\n";
            message += emptyFields.join("\n");
            showError(message);
            return false;
        }

        hideError();
        return true;
    }

    function canProceedToNextTab(currentTab) {
        var inputs = document.getElementById('step' + currentTab).querySelectorAll('input[required], select[required]');
        var isValid = true;
        var emptyFields = [];

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value.trim() === "") {
                isValid = false;
                var label = inputs[i].previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    emptyFields.push(label.textContent.trim());
                }
            }
        }

        if (!isValid) {
            var message = "Please fill in the following required fields:\n\n";
            message += emptyFields.join("\n");
            showError(message);
            return false;
        }

        hideError();
        return true;
    }

    // Modify the showTab function to include validation
    function showTab(step) {
        // Get current active tab number
        var currentTab = document.querySelector('.tab.active').textContent.match(/\d+/)[0];

        // If trying to go to next tab, validate current tab first
        if (step > currentTab && !canProceedToNextTab(currentTab)) {
            return;
        }

        // Hide all tab contents and remove 'active' class from all tabs
        const tabContents = document.querySelectorAll('.tab-content');
        const tabs = document.querySelectorAll('.tab');
        tabContents.forEach(content => content.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));

        // Show the selected tab content and add 'active' class to clicked tab
        document.getElementById('step' + step).classList.add('active');
        tabs[step - 1].classList.add('active');

        hideError();
    }

    // Attach the validateForm function to form submission
    document.getElementById("registration-form").onsubmit = validateForm;
</script>
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
<script>
    function updateMembershipInfo() {
        var select = document.getElementById("membership");
        var div = document.getElementById("membershipInfo");
        var selectedOption = select.value;

        // Base prices for each membership type
        const basePrices = {
            "Standard": 70,
            "Premium": 120,
            "Vip": 150,
            "Free": 0
        };

        // Update the display content
        if (selectedOption === "Free") {
            div.innerHTML = "<h3>Basic membership with access to purchase tickets at regular prices. No discounts or reward points available.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'></div>";
        } else if (selectedOption === "Standard") {
            div.innerHTML = "<h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a 15% discounted price. Applies to one person</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>";
        } else if (selectedOption === "Premium") {
            div.innerHTML = "<h3>Perfect for families! This membership includes a 25% discount, offering a cost-effective way to enjoy the zoo together.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        } else if (selectedOption === "Vip") {
            div.innerHTML = "<h3>Experience the zoo like never before! VIP members get a 40% discount, access to exclusive events, behind-the-scenes tours, and discounts on tickets, food, and gift shop purchases.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='adult' src='../assets/img/adult.png' alt='adult' width='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        }

        // Update both price displays
        document.getElementById("base-price").textContent = `$${basePrices[selectedOption].toFixed(2)}`;
        document.getElementById("selected-type").textContent = selectedOption;
        document.getElementById("total-base-price").textContent = `$${basePrices[selectedOption].toFixed(2)}`;
        document.getElementById("total-cost").textContent = `$${basePrices[selectedOption].toFixed(2)}`;

        // Update registration bonus display
        const registrationBonus = selectedOption === 'Free' ? '0 points' : '500 points';
        document.getElementById("registration-bonus").textContent = registrationBonus;
    }

    // Call updateMembershipInfo on page load to set initial values
    document.addEventListener('DOMContentLoaded', function() {
        updateMembershipInfo();
    });
</script>

<style>
    .payment-summary {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .membership-details {
        margin-top: 15px;
    }

    .membership-details p {
        margin: 10px 0;
        font-size: 16px;
    }

    .membership-details strong {
        display: inline-block;
        width: 200px;
    }
</style>