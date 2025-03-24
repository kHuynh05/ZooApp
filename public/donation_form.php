<head>
    <link rel = "stylesheet" href = "../assets/css/donation_form.css">
</head>
<?php
//Include the database connection
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $fname = $_POST['user_fname'];
    $lname = $_POST['user_lname'];
    $address1 = $_POST['user-address1'];
    $address2 = $_POST['user-address2'];
    $city = $_POST['user-city'];
    $state = $_POST['user-state'];
    $zipcode = $_POST['user-zipcode'];
    $packet = $_POST['packet_name'];
    $animal = $_POST['animal_name'];
    $comment = $_POST['user-message'];
    $amount = $_POST['user-money'];

    $sql = "INSERT INTO donations
        (fname, lname, addr, addr2, city, state, zcode, packet, animal, comment, donation_amount)
        VALUES
        (:fname, :lname, :addr, :addr2, :city, :state, :zcode, :packet, :animal, :comment, :donation_amount)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fname' => $fname,
        ':lname' => $lname,
        ':addr' => $address1, 
        ':addr2' => $address2, 
        ':city' => $city, 
        ':state' => $state, 
        ':zcode' => $zipcode, 
        ':packet' => $packet,
        ':animal' => $animal, 
        ':comment' => $comment,
        ':donation_amount' => $amount 
    ]);

    echo "<p style='color:green; text-align:center;'>Donation submitted successfully!</p>";
}
?>

<div class = "container">
    <?php include('../includes/navbar.php');?>
    <div class = "homepage">
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
                        <div class = "form-packet">
                            <div class = "input-group">
                                <label for = "packet">Packets</label>
                                <select id = "packet_id" name = "packet_name" required>
                                    <option value = "" disabled selected>Select a packet</option>
                                    <?php
                                    $packet = [
                                        "Friends" => "Friend",
                                        "Guardians" => "Guardian",
                                        "Protectors" => "Protector",
                                        "Classroom" => "Classroom Package",
                                        "Birthday" => "Birthday Package"
                                    ];

                                    foreach ($packet as $abbr => $name){
                                        echo "<option value ='$abbr'>$name</option>";
                                    }
                                    ?>
                                </select>
                                <?php 
                                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                                    if (isset($_POST["packet_name"])){
                                        $selected_packet = $_POST["packet_name"];
                                        echo "<p>You selected: ". htmlspecialchars($packet[$selected_packet]) . " ($selected_packet)</p>";
                                    }
                                }
                                ?>

                            </div>
                            <div class = "input-group">
                                <label for = "animal">Animal</label>
                                <select id = "animal_id" name = "animal_name" required>
                                    <option value = "" disabled selected>Select an animal</option>
                                    <?php
                                    $animal = [
                                        "Lion" => "Lion",
                                        "Seaturtle" => "Seaturtle",
                                        "Chimp" => "Chimpanzee"
                                    ];

                                    foreach ($animal as $abbr => $name){
                                        echo "<option value ='$abbr'>$name</option>";
                                    }
                                    ?>
                                </select>
                                <?php
                                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                                    if (isset($_POST["animal_name"])){
                                        $selected_animal = $_POST["animal_name"];
                                        echo "<p>You selected: " . htmlspecialchars($animal[$selected_animal]) . " ($selected_animal)</p>";
                                    }
                                }
                                ?>
                            </div>
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