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
                <div class = "donate-btn">
                    <a href = "adopt.php">
                        <button>Donate Now</button>
                    </a>
                </div>
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
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
