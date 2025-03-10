<?php
// Include the database connection
include '../config/database.php'; // Or however you connect to your database
include '../scripts/authorize.php';
// Check if user is a member and redirect accordingly
?>
<head>
    <link rel="stylesheet" href="../assets/css/ticket.css">
</head>

<div class="container">
    <?php include('../includes/navbar.php'); ?>
    
    <div class="ticket-section">
        <h1 class="ticket-header">Buy Tickets</h1>
        
        <div class="ticket-options">
            <a href="onetimeticket.php" class="ticket-box">
                <h2>General Admission</h2>
            </a>
            
            <a href="<?php echo $is_member ? 'memberPortal.php' : 'login.php'; ?>" class="ticket-box">
                <h2>Members Ticket</h2>
            </a>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>
