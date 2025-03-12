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
    $ticket_type = $_POST['ticket_type'];

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
        // Optionally, redirect the user or do something else
        setTimeout(function() {
            location.href = 'login.php'; // Redirect to login page (adjust this to your needs)
        }, 2000);  // Delay before redirecting
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
        $sql_customer = "INSERT INTO customers (
            cust_email, date_of_birth, sex, ticket_type, first_name, last_name
        ) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt_customer = $conn->prepare($sql_customer)) {
            // Bind parameters for customer table
            $stmt_customer->bind_param(
                "ssssss",
                $email,
                $dob,
                $gender,
                $ticket_type,
                $first_name,
                $last_name
            );

            // Execute query for customer
            if ($stmt_customer->execute()) {
                $cust_id = $stmt_customer->insert_id; // Get the last inserted cust_id

                // Insert query for member data
                $sql_member = "INSERT INTO members (
                    password, 
                    membership_start_date, membership_end_date, 
                    membership_type, membership_status
                ) VALUES (?, ?, ?, ?, ?)";


                if ($stmt_member = $conn->prepare($sql_member)) {
                    // Bind parameters for member table
                    $membership_status = 'active';
                    $stmt_member->bind_param(
                        "sssss",
                        $password,
                        $membership_start_date,
                        $membership_end_date,
                        $ticket_type,
                        $membership_status // Membership is active by default
                    );

                    // Execute query for member
                    if ($stmt_member->execute()) {
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
            } else {
                echo "<script>alert('Error saving customer data: " . $stmt_customer->error . "');</script>";
            }

            $stmt_customer->close();
        }
        $conn->close();
    }
}
?>

<head>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>

<?php include('../includes/navbar.php'); ?>
<h1>REGISTER FOR ZOO MEMBERSHIP</h1>
<div class="container">
    <!-- Left Sidebar for Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="showTab(1)">Step 1: Basic Info</div>
        <div class="tab" onclick="showTab(2)">Step 2: Membership</div>
        <div class="tab" onclick="showTab(3)">Step 3: Review</div>
    </div>

    <!-- Right Content Area -->
    <form class="form-container" id="registration-form" method="POST">
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
                    <input type="text" id="state" name="state" required>
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
                <input type="date" id="dob" name="date_of_birth" required>
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
                <input type="password" id="password" name="cust_password" required>
            </div>
            <div class="form-group">
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" id="confirmpassword" name="confirm_password" required>
            </div>

            <!-- Address Section -->
        </div>

        <!-- Step 2: Membership -->
        <div class="tab-content" id="step2">
            <h2>Step 2: Choose Membership</h2>
            <div class="form-group">
                <label for="membership">Choose Membership Type</label>
                <select id="membership" name="ticket_type" onchange="changeContent()" required>
                    <option value="standard">Standard</option>
                    <option value="family">Family</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
            <div id="membershipInfo">
                You selected Option 1! Here is the content for Option 1.
            </div>
        </div>

        <!-- Step 3: Review -->
        <div class="tab-content" id="step3">
            <h2>Step 3: Review</h2>
            <button type="submit">Submit Registration</button>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
<script>
    function changeContent() {
        var select = document.getElementById("membership");
        var div = document.getElementById("membershipInfo");
        var selectedOption = select.value;

        // Change div content based on the selected option
        if (selectedOption === "standard") {
            div.innerHTML = "Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a discounted price.";
        } else if (selectedOption === "family") {
            div.innerHTML = " Perfect for families! This membership includes two adults and up to three children, offering a cost-effective way to enjoy the zoo together.";
        } else if (selectedOption === "vip") {
            div.innerHTML = "Experience the zoo like never before! VIP members get unlimited entry, access to exclusive events, behind-the-scenes tours, and discounts on tickets, food, and gift shop purchases.";
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

    function showTab(step) {
        // Hide all tab contents and remove 'active' class from all tabs
        const tabContents = document.querySelectorAll('.tab-content');
        const tabs = document.querySelectorAll('.tab');
        tabContents.forEach(content => content.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));

        // Show the selected tab content and add 'active' class to clicked tab
        document.getElementById('step' + step).classList.add('active');
        tabs[step - 1].classList.add('active');
    }

    function validateForm() {
        var inputs = document.querySelectorAll('input[required], select[required]');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value === "") {
                alert("Please fill out all required fields.");
                return false; // Prevent form submission
            }
        }

        // Check if password and confirm password match
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirmpassword").value;

        if (password !== confirmPassword) {
            alert("Password and Confirm Password do not match.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }

    // Attach the validateForm function to form submission
    document.getElementById("registration-form").onsubmit = validateForm;
</script>