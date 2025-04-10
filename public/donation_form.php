<head>
    <link rel="stylesheet" href="../assets/css/donation_form.css">
</head>

<?php
// Include database connection
include '../config/database.php';

// Define packet prices at the top of the file
$packet_prices = array(
    'Friends' => 25,
    'Guardians' => 50,
    'Protectors' => 100,
    'Classroom' => 75,
    'Birthday' => 150
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $first_name     = trim($_POST['fname']);
    $last_name      = trim($_POST['lname']);
    $email          = trim($_POST['cust_email']); // Changed to match form field name
    $address_line1  = trim($_POST['addr1']);
    $address_line2  = trim($_POST['addr2']) ?? '';
    $city           = trim($_POST['city']);
    $state          = trim($_POST['state']);
    $zip_code       = trim($_POST['zcode']);
    $packet         = trim($_POST['packet']);
    $animal         = trim($_POST['animal']);
    $comment        = trim($_POST['comment']) ?? '';
    $amount         = $packet_prices[$packet];

    // Validate required fields
    if (
        empty($first_name) || empty($last_name) || empty($email) || 
        empty($address_line1) || empty($city) || empty($state) || 
        empty($zip_code) || empty($packet) || empty($animal)
    ) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit;
    }

    try {
        // STEP 1: Check if customer exists
        $sql = "SELECT cust_id FROM customers WHERE cust_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);  // Changed from "ss" to "s"
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Customer doesn't exist, insert new customer
            $sql = "INSERT INTO customers (cust_email, first_name, last_name) 
                   VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", 
                $email,
                $first_name,
                $last_name
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create new customer: " . $stmt->error);
            }
            
            $cust_id = $stmt->insert_id;
            $stmt->close();
        } else {
            $row = $result->fetch_assoc();
            $cust_id = $row['cust_id'];
            $stmt->close();
        }

        // Add error logging to help debug
        error_log("Customer ID for donation: " . $cust_id);
        error_log("Amount for donation: " . $amount);

        // STEP 2: Insert donation
        $transaction_date = date("Y-m-d H:i:s");
        
        $sql = "INSERT INTO donations (
            transaction_date, cust_id, donation_amount, fname, lname, addr1, addr2, 
            city, state, zcode, packet, animal, comment
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sidssssssssss",
            $transaction_date,
            $cust_id,
            $amount,
            $first_name,
            $last_name,
            $address_line1,
            $address_line2,
            $city,
            $state,
            $zip_code,
            $packet,
            $animal,
            $comment
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error . " SQL State: " . $stmt->sqlstate);
        }

        $transaction_number = $stmt->insert_id;
        $stmt->close();
        
        echo "<script>
            window.location.href = 'donation-receipt.php?transaction=" . $transaction_number . 
            "&fname=" . urlencode($first_name) . 
            "&lname=" . urlencode($last_name) . 
            "&email=" . urlencode($email) . 
            "&addr1=" . urlencode($address_line1) . 
            "&addr2=" . urlencode($address_line2) . 
            "&city=" . urlencode($city) . 
            "&state=" . urlencode($state) . 
            "&zcode=" . urlencode($zip_code) . 
            "&packet=" . urlencode($packet) . 
            "&amount=" . urlencode($amount) . "';3
        </script>";

    } catch (Exception $e) {
        error_log("Donation Error: " . $e->getMessage());
        echo "<script>alert('Transaction failed: " . addslashes($e->getMessage()) . "');</script>";
    }

    $conn->close();
}

?>

<div class="container">
    <?php include('../includes/navbar.php'); ?>
    <div class="homepage">
        <form class="form-container" method="post" action="donation_form.php">
            <div class="form-box">
                <h1 class="form-header">Donation Request Form</h1>

                <!-- Customer Information -->
                <div class="form-group">
                    <label>Customer Information</label>
                    <div class="form-customer">
                        <input type="text" name="fname" placeholder="First Name" required>
                        <input type="text" name="lname" placeholder="Last Name" required>
                        <input type="email" name="cust_email" placeholder="Email" required>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label>Address</label>
                    <div class="form-address">
                        <input type="text" name="addr1" placeholder="Address Line 1" required>
                        <input type="text" name="addr2" placeholder="Address Line 2">
                    </div>
                </div>

                <!-- City, State, Zip Code -->
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>
                <div class="form-group">
                    <label>State</label>
                    <select name="state" required>
                        <option value="" disabled selected>Select State</option>
                        <?php
                        $states = array(
                            'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas',
                            'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut',
                            'DE'=>'Delaware', 'FL'=>'Florida', 'GA'=>'Georgia',
                            'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana',
                            'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana',
                            'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts',
                            'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi',
                            'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska',
                            'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey',
                            'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina',
                            'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma',
                            'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island',
                            'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee',
                            'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia',
                            'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin',
                            'WY'=>'Wyoming'
                        );
                        foreach($states as $abbr => $name) {
                            echo "<option value=\"$abbr\">$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Zip Code</label>
                    <input type="text" name="zcode" pattern="[0-9]{5}" title="Five digit zip code" required>
                </div>

                <!-- Packet Selection -->
                <div class="form-group">
                    <label>Support Package</label>
                    <select name="packet" required>
                        <option value="" disabled selected>Select a Support Package</option>
                        <?php
                        foreach($packet_prices as $name => $price) {
                            echo "<option value=\"$name\">$name (\$$price)</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Animal Selection -->
                <div class="form-group">
                    <label>Animal</label>
                    <select name="animal" required>
                        <option value="" disabled selected>Select an Animal</option>
                        <option value="Lion">Lion</option>
                        <option value="Seaturtle">Sea Turtle</option>
                        <option value="Chimp">Chimpanzee</option>
                    </select>
                </div>

                <!-- Comment -->
                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment"></textarea>
                </div>

                <button type="submit">Submit Donation</button>
            </div>
        </form>
    </div>
</div> 