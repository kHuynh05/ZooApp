<?php
// Get all parameters from URL
$transaction = isset($_GET['transaction']) ? htmlspecialchars($_GET['transaction']) : '';
$fname = isset($_GET['fname']) ? htmlspecialchars($_GET['fname']) : '';
$lname = isset($_GET['lname']) ? htmlspecialchars($_GET['lname']) : '';
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$addr1 = isset($_GET['addr1']) ? htmlspecialchars($_GET['addr1']) : '';
$addr2 = isset($_GET['addr2']) ? htmlspecialchars($_GET['addr2']) : '';
$city = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
$state = isset($_GET['state']) ? htmlspecialchars($_GET['state']) : '';
$zcode = isset($_GET['zcode']) ? htmlspecialchars($_GET['zcode']) : '';
$packet = isset($_GET['packet']) ? htmlspecialchars($_GET['packet']) : '';
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '';

// Define packet descriptions
$packet_descriptions = array(
    'Friends' => 'Adoption Certificate, Animal Fact Sheet, 5x7 Photo of Adopted Animal Zootopia E-Newsletter',
    'Guardians' => 'Adoption Certificate, Animal Fact Sheet, 5x7 Photo of Adopted Animal, Zootopia E-Newsletter, Animal Plush Toy',
    'Protectors' => 'Adoption Certificate, Animal Fact Sheet, 5x7 Photo of Adopted Animal, Zootopia E-Newsletter, Animal Plush Toy, Two Zootopia Day Passes',
    'Classroom' => '25 Adoption Certificate, 25 Animal Fact Sheet, 5x7 Photo of Adopted Animal, Zootopia E-Newsletter, Animal Plush Toy',
    'Birthday' => 'Adoption Certificate, Animal Fact Sheet, 5x7 Photo of Adopted Animal, Zootopia E-Newsletter, Animal Plush Toy, 4 Zootopia Day Passes, Zootopia Birthday Button, Personalized Birthday Video'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Receipt - Zoo</title>
    <link rel="stylesheet" href="../assets/css/donation_form.css">
    <style>
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .receipt-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .receipt-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .receipt-section {
            margin-bottom: 20px;
        }

        .receipt-section h2 {
            color: #34495e;
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .receipt-section p {
            margin: 5px 0;
            color: #2c3e50;
        }

        .packet-description {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .packet-description h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .packet-description p {
            color: #34495e;
            line-height: 1.6;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .receipt-footer p {
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-footer-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .print-button {
            display: inline-block;
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .print-button:hover {
            background-color: #2980b9;
        }

        .back-button {
            display: inline-block;
            padding: 10px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-left: auto;  /* This pushes the button to the right */
        }

        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/navbar.php'); ?>
        
        <div class="receipt-container">
            <div class="receipt-header">
                <h1>Donation Receipt</h1>
                <p>Transaction #: <?php echo $transaction; ?></p>
                <p>Date: <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="receipt-details">
                <div class="receipt-section">
                    <h2>Donor Information</h2>
                    <p><strong>Name:</strong> <?php echo $fname . ' ' . $lname; ?></p>
                    <p><strong>Email:</strong> <?php echo $email; ?></p>
                    <p><strong>Address:</strong><br>
                        <?php echo $addr1; ?><br>
                        <?php if ($addr2) echo $addr2 . '<br>'; ?>
                        <?php echo $city . ', ' . $state . ' ' . $zcode; ?>
                    </p>
                </div>

                <div class="receipt-section">
                    <h2>Donation Details</h2>
                    <p><strong>Package:</strong> <?php echo $packet; ?></p>
                    <p><strong>Amount:</strong> $<?php echo $amount; ?></p>
                    <p><strong>Transaction ID:</strong> <?php echo $transaction; ?></p>
                </div>
            </div>

            <div class="packet-description">
                <h3>Package Contents</h3>
                <p><?php echo $packet_descriptions[$packet]; ?></p>
            </div>

            <div class="receipt-footer">
                <p>Thank you for your generous donation to our zoo!</p>
                <p>This receipt serves as proof of your donation for tax purposes.</p>
                <div class="receipt-footer-buttons">
                    <button class="print-button" onclick="window.print()">Print Receipt</button>
                    <a href="homepage.php" class="back-button">Return to Home</a>
                </div>
            </div>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html> 