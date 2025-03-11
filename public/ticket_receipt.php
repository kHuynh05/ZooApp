<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['transaction_data'])) {
    header("Location: onetimeticket.php");
    exit();
}

$data = $_SESSION['transaction_data'];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Receipt - Zoo Tickets</title>
    <link rel="stylesheet" href="../assets/css/ticket_receipt.css">
</head>

<div class="container">
    <?php include('../includes/navbar.php'); ?>
    
    <div class="receipt-container">
        <h1 class="receipt-title">Ticket Receipt</h1>
        
        <?php
        // Fetch customer info
        $stmt = $conn->prepare("SELECT first_name, last_name FROM customers WHERE cust_id = ?");
        $stmt->bind_param("i", $data['cust_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        
        echo "<div class='customer-info'>
                <h2>Customer Information</h2>
                <p>Name: " . htmlspecialchars($customer['first_name']) . " " . 
                htmlspecialchars($customer['last_name']) . "</p>
                <p>Visit Date: " . date('F j, Y', strtotime($data['reservation_date'])) . "</p>
              </div>";
        
        // Fetch tickets
        $stmt = $conn->prepare("SELECT transaction_number, ticket_type FROM tickets WHERE cust_id = ?");
        $stmt->bind_param("i", $data['cust_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<div class='tickets-container'>";
        while ($ticket = $result->fetch_assoc()) {
            echo "<div class='ticket'>
                    <h3>Ticket Type: {$ticket['ticket_type']}</h3>
                    <p>Transaction #: {$ticket['transaction_number']}</p>
                    <div class='barcode'>
                        <img src='https://barcode.tec-it.com/barcode.ashx?data={$ticket['transaction_number']}&code=Code128' alt='Barcode'>
                    </div>
                  </div>";
        }
        echo "</div>";
        
        // Clear session data
        unset($_SESSION['transaction_data']);
        ?>
        
        <button onclick="window.print()" class="print-button">Print Tickets</button>
    </div>
    
    <?php include('../includes/footer.php'); ?>
</div>