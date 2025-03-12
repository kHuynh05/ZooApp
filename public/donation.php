<head>
    <link rel = "stylesheet" href = "../assets/css/donation.css">
</head>
<?php
//Include the database connection
include '../config/database.php';

?>
<div class = "container">
    <?php include('../includes/navbar.php');?>
    <div class = "homepage">
        <div class = "frontpage">
            <div class = "frontpage-header">
                <span class = "donate-title">Donation Requests</span>
            </div>
        </div>
        <div class="image-container">
            <img src="../assets/img/giraffe-donate2.jpg">
        </div>
        <div class="donate-container">
            <div class = "donate-boxes">
                <h1 class = "donate-box-header">Donate Appeal</h1>
                <span class = "donate-text">
                    At <b>Zootopia</b>, we are committed to providing a safe, enriching environment for our animals while educating and
                     inspiring visitors about wildlife conservation. Your generous donations play a crucial role in ensuring our zoo thrives—helping us 
                     provide top-quality animal care, enrichment programs, veterinary services, and habitat improvements.
                </span>
                <span class = "donate-text">By donating, you contribute to:</span>
                <ul>
                    <li>Nutritous food and medical care for our animals</li>
                    <li>Conservation efforts to protect endangered species</li>
                    <li>Educational programs for schools and families</li>
                    <li>Maintenance and expansion of nautralistic habits</li>
                </ul>
                <span class = "donate-text">
                    Every contribution, big or small, makes a difference in the lives of our animals. Join us in protecting and preserving wildlife!
                     <b>Donate today and be a part of our mission at Zootopia</b>
                </span>
            </div>
            <div class = "donate-boxes">
                <h1 class = "donate-box-header">Donate Requirements and Guidelines</h1>
                <span class = "donate-text">
                    We appreciate your willingness to support <b>Zootopia</b>! To ensure a smooth donation process, please review the following guidelines:
                </span>
                <ul>
                    <li>Donation: We accept online donations via credit/debit cards, and PayPal.</li>
                    <li>Tax Deductibility: Zootopia is registered nonprofit, and your donations may be tax-deductible. A donation receipt will be provided upon request.</li>
                </ul>
                <span class = "donate-text">
                    Your support allows us to continue providing excellent care for our animals and promoting conservation efforts.
                     <b>Thank you for being a part of Zootopia’s mission</b>!
                </span>
            </div>
        </div>
        <form class = "form-container" method = "post">
            <div class = "form-box">
                <h1 class = "form-header">Donation request form</h1>
                <div class = "form-thing">
                    <div class = "form-group">
                        <label for = "name">Full Name</label>
                        <div class = "form-name">
                            <input type = "text" id = "user-name" name = "user-fname" placeholder = "First Name", required>
                            <input type = "text" id = "user-name" name = "user-lname" placeholder = "Last Name", required>
                        </div>
                    </div>
                    <div class = "form-group">
                        <label for = "Address">Address</label>
                        <div class = "form-address">
                            <input type="text" id = "user-address" name = "user-address1" placeholder = "Address Line 1" required> 
                            <input type="text" id = "user-address" name = "user-address2" placeholder = "Address Line 2">
                        </div>
                        <div class = "form-address1">
                            
                            <div class = "input-group">
                                <label for = "city">City</label>
                                <input type="text" id = "user-address" name = "user-city" placeholder = "City" required>
                            </div>
                            
                            <div class = "input-group">
                                <label for = "city">State</label>
                                <select id = "state" name = "user-state" required>
                                    <option value="" disabled selected>Select a state</option>
                                    <?php
                                    $states = [
                                        "AL" => "Alabama",
                                        "AK" => "Alaska",
                                        "AZ" => "Arizona",
                                        "AR" => "Arkansas",
                                        "CA" => "California",
                                        "CO" => "Colorado",
                                        "CT" => "Connecticut",
                                        "DE" => "Delaware",
                                        "FL" => "Florida",
                                        "GA" => "Georgia",
                                        "HI" => "Hawaii",
                                        "ID" => "Idaho",
                                        "IL" => "Illinois",
                                        "IN" => "Indiana",
                                        "IA" => "Iowa",
                                        "KS" => "Kansas",
                                        "KY" => "Kentucky",
                                        "LA" => "Louisiana",
                                        "ME" => "Maine",
                                        "MD" => "Maryland",
                                        "MA" => "Massachusetts",
                                        "MI" => "Michigan",
                                        "MN" => "Minnesota",
                                        "MS" => "Mississippi",
                                        "MO" => "Missouri",
                                        "MT" => "Montana",
                                        "NE" => "Nebraska",
                                        "NV" => "Nevada",
                                        "NH" => "New Hampshire",
                                        "NJ" => "New Jersey",
                                        "NM" => "New Mexico",
                                        "NY" => "New York",
                                        "NC" => "North Carolina",
                                        "ND" => "North Dakota",
                                        "OH" => "Ohio",
                                        "OK" => "Oklahoma",
                                        "OR" => "Oregon",
                                        "PA" => "Pennsylvania",
                                        "RI" => "Rhode Island",
                                        "SC" => "South Carolina",
                                        "SD" => "South Dakota",
                                        "TN" => "Tennessee",
                                        "TX" => "Texas",
                                        "UT" => "Utah",
                                        "VT" => "Vermont",
                                        "VA" => "Virginia",
                                        "WA" => "Washington",
                                        "WV" => "West Virginia",
                                        "WI" => "Wisconsin",
                                        "WY" => "Wyoming"
                                    ];

                                    foreach ($states as $abbr => $name) {
                                        echo "<option value='$abbr'>$name</option>";
                                    }
                                    ?>
                                                                
                                </select>
                                <?php
                                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                    if (isset($_POST["user-state"])) {
                                        $selected_state = $_POST["user-state"];
                                        echo "<p>You selected: " . htmlspecialchars($states[$selected_state]) . " ($selected_state)</p>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <label for = "zipcode">ZIP code <span class = "warning">Valid ZIP code: 5 digits</span></label>
                        <div class = "form-address2">
                            <input type="text" id = "user-address" name = "user-zipcode" placeholder = "ZIP Code" required
                            pattern = "[0-9]{5}(-[0-9]{4})?" maxlength = "10">
                            <?php
                            if ($_Server["REQUEST_METHOD"] == "POST"){
                                if (isset($_POST["user-zipcode"])){
                                    $zip = $_POST["user-zipcode"];

                                    if(!preg_match("/^[0-9]{5}(-[0-9]{4})?$/", $zip)){
                                        echo "<p style = 'color: red;'>Error: please enter a valid ZIP Code</p>";
                                    }
                                }
                            }
                            ?>  
                        </div>  
                    </div>
                    <div class = "form-group">
                        <label for = "Comment">Comment</label>
                        <textarea id="user-message" name="user-message" rows="4" placeholder="Enter your message here..."></textarea>
                    </div>
                    <div class = "form-group">
                        <label for = "amount">Payment Amount</label>
                        <div class = "form-amount">
                            <input type = "number" id = "user-amount" name = "user-money" placeholder = "Enter an amount USD" required>
                            <?php
                            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                                if (isset($_POST["user-money"])){
                                    $input = $_POST["user-money"];

                                    if (!is_numeric($input) || floatval($input) <= 0){
                                        echo "<p style = 'color: red;'>Error: Only positive whole numbers are allowed!</p>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <button type="submit"> Donate Now </button>
            </div>
        </form>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
