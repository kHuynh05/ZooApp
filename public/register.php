<head>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>

<?php
// Include database connection
include '../config/database.php';  // Make sure the path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['cust_name'];
    $last_name = $_POST['last_name'];  // Assuming you have a last name field
    $email = $_POST['cust_email'];
    $password = password_hash($_POST['cust_password'], PASSWORD_BCRYPT); // Hash the password
    $confirm_password = $_POST['confirm_password'];
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['sex'];
    $is_member = isset($_POST['is_member']) ? 1 : 0; // If member is checked
    $ticket_type = $_POST['ticket_type'];
    $visit_date = $_POST['visit_date'];
    
    // Address data
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    // Set member start date if membership is selected
    $membership_start_date = $is_member ? date("Y-m-d") : NULL;  // Today's date
    // Set membership end date if membership is selected (1 year later)
    $membership_end_date = $is_member ? date("Y-m-d", strtotime("+1 year")) : NULL;

    // Insert query for member data
    $sql_member = "INSERT INTO members (
        first_name, last_name, email, password, 
        membership_start_date, membership_end_date, 
        membership_type, membership_status, date_of_birth, sex
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt_member = $conn->prepare($sql_member)) {
        // Bind parameters for member table
        $stmt_member->bind_param("ssssssssss", 
            $first_name, 
            $last_name, 
            $email, 
            $password, 
            $membership_start_date, 
            $membership_end_date, 
            $ticket_type, 
            $membership_status = 'active', // Membership is active by default
            $dob, 
            $gender
        );

        // Execute query
        if ($stmt_member->execute()) {
            $cust_id = $stmt_member->insert_id; // Get the last inserted cust_id

            // Insert query for customer address
            $sql_address = "INSERT INTO cust_address (
                cust_id, address_line1, address_line2, city, 
                state, zip_code, country
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_address = $conn->prepare($sql_address)) {
                // Bind parameters for address table
                $stmt_address->bind_param("issssss", 
                    $cust_id, 
                    $address_line1, 
                    $address_line2, 
                    $city, 
                    $state, 
                    $zip_code, 
                    $country
                );

                // Execute query for address
                if ($stmt_address->execute()) {
                    echo "<script>alert('Registration and address saved successfully!'); window.location.href = 'login.html';</script>";
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
    $conn->close();
}
?>
<?php include('../includes/navbar.php'); ?>
<h1>REGISTER FOR ZOO MEMBERSHIP</h1>
<div class="container">
    <!-- Left Sidebar for Tabs -->
    
    <div class="tabs">
        <div class="tab active" onclick="showTab(1)">Step 1: Basic Info</div>
        <div class="tab" onclick="showTab(2)">Step 2: Membership</div>
        <div class="tab" onclick="showTab(3)">Step 3: Payment</div>
    </div>

    <!-- Right Content Area -->
    <form class="form-container" id="registration-form">
        <!-- Step 1: Basic Info -->
        <div class="tab-content active" id="step1">
            <h2>Step 1: Basic Information</h2>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="cust_name" required>
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
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="sex" required>
                    <option value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-address">
                <label for="street">Street</label>
                <input type="text" id="street" name="street" required>

                <label for="city">city</label>
                <input type="text" id="city" name="city" required>

                <label for="country">Country</label>
                <select id="country" name="country" required>
                    <option value="">Select country</option>
                </select>

                <label for="state">State</label>
                <select id="state" name="state" required>
                    <option value="">Select state</option>
                </select>

                <label for="zip">Zipcode</label>
                <input type="text" id="zip" name="zip" required>

            </div>
        </div>

        <!-- Step 2: Membership -->
        <div class="tab-content" id="step2">
            <h2>Step 2: Choose Membership</h2>
            <div class="form-group">
                <label for="membership">Choose Membership Type</label>
                <select id="membership" name="membership_type" onchange="changeContent()" required>
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
            <button></button>
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
                div.innerHTML = "You selected Option 1! Here is the content for Option 1.";
            } else if (selectedOption === "family") {
                div.innerHTML = "You selected Option 2! Here is the content for Option 2.";
            } else if (selectedOption === "vip") {
                div.innerHTML = "You selected Option 3! Here is the content for Option 3.";
            }
        }
    const countries = [
            { code: "USA", name: "United States" },
            { code: "CAN", name: "Canada" },
            { code: "GBR", name: "United Kingdom" },
            { code: "AUS", name: "Australia" },
            { code: "IND", name: "India" },
            { code: "DEU", name: "Germany" },
            { code: "FRA", name: "France" },
            { code: "ITA", name: "Italy" },
            { code: "BRA", name: "Brazil" },
            { code: "JPN", name: "Japan" },
            { code: "CHN", name: "China" },
            { code: "MEX", name: "Mexico" },
            { code: "RUS", name: "Russia" },
            { code: "ZAF", name: "South Africa" },
            { code: "KOR", name: "South Korea" },
            { code: "ARG", name: "Argentina" },
            { code: "ESP", name: "Spain" },
            { code: "POL", name: "Poland" },
            { code: "NLD", name: "Netherlands" },
            { code: "CHE", name: "Switzerland" },
            { code: "BEL", name: "Belgium" },
            { code: "SWE", name: "Sweden" },
            { code: "NOR", name: "Norway" },
            { code: "FIN", name: "Finland" },
            { code: "DNK", name: "Denmark" },
            { code: "AUT", name: "Austria" },
            { code: "GRE", name: "Greece" },
            { code: "TUR", name: "Turkey" },
            { code: "EGY", name: "Egypt" },
            { code: "ARE", name: "United Arab Emirates" },
            { code: "SAU", name: "Saudi Arabia" },
            { code: "ISR", name: "Israel" },
            { code: "CHL", name: "Chile" },
            { code: "COL", name: "Colombia" },
            { code: "PER", name: "Peru" },
            { code: "VNM", name: "Vietnam" },
            { code: "IDN", name: "Indonesia" },
            { code: "THA", name: "Thailand" },
            { code: "PHL", name: "Philippines" },
            { code: "MYS", name: "Malaysia" },
            { code: "SGP", name: "Singapore" },
            { code: "NZL", name: "New Zealand" },
            { code: "KEN", name: "Kenya" },
            { code: "UGA", name: "Uganda" },
            { code: "TAN", name: "Tanzania" },
            { code: "ETH", name: "Ethiopia" },
            { code: "NGA", name: "Nigeria" },
            { code: "GHA", name: "Ghana" }
        ];

        // Get the select element
        const select = document.getElementById('country');

        // Loop through the array and add each country as an option
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.code;
            option.textContent = country.name;
            select.appendChild(option);
        });
        const states = [
            { code: "AL", name: "Alabama" },
            { code: "AK", name: "Alaska" },
            { code: "AZ", name: "Arizona" },
            { code: "AR", name: "Arkansas" },
            { code: "CA", name: "California" },
            { code: "CO", name: "Colorado" },
            { code: "CT", name: "Connecticut" },
            { code: "DE", name: "Delaware" },
            { code: "FL", name: "Florida" },
            { code: "GA", name: "Georgia" },
            { code: "HI", name: "Hawaii" },
            { code: "ID", name: "Idaho" },
            { code: "IL", name: "Illinois" },
            { code: "IN", name: "Indiana" },
            { code: "IA", name: "Iowa" },
            { code: "KS", name: "Kansas" },
            { code: "KY", name: "Kentucky" },
            { code: "LA", name: "Louisiana" },
            { code: "ME", name: "Maine" },
            { code: "MD", name: "Maryland" },
            { code: "MA", name: "Massachusetts" },
            { code: "MI", name: "Michigan" },
            { code: "MN", name: "Minnesota" },
            { code: "MS", name: "Mississippi" },
            { code: "MO", name: "Missouri" },
            { code: "MT", name: "Montana" },
            { code: "NE", name: "Nebraska" },
            { code: "NV", name: "Nevada" },
            { code: "NH", name: "New Hampshire" },
            { code: "NJ", name: "New Jersey" },
            { code: "NM", name: "New Mexico" },
            { code: "NY", name: "New York" },
            { code: "NC", name: "North Carolina" },
            { code: "ND", name: "North Dakota" },
            { code: "OH", name: "Ohio" },
            { code: "OK", name: "Oklahoma" },
            { code: "OR", name: "Oregon" },
            { code: "PA", name: "Pennsylvania" },
            { code: "RI", name: "Rhode Island" },
            { code: "SC", name: "South Carolina" },
            { code: "SD", name: "South Dakota" },
            { code: "TN", name: "Tennessee" },
            { code: "TX", name: "Texas" },
            { code: "UT", name: "Utah" },
            { code: "VT", name: "Vermont" },
            { code: "VA", name: "Virginia" },
            { code: "WA", name: "Washington" },
            { code: "WV", name: "West Virginia" },
            { code: "WI", name: "Wisconsin" },
            { code: "WY", name: "Wyoming" }
        ];

        // Get the select element
        select = document.getElementById('state');

        // Loop through the array and add each state as an option
        states.forEach(state => {
            const option = document.createElement('option');
            option.value = state.code;
            option.textContent = state.name;
            select.appendChild(option);
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
</script>


