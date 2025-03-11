<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['ticket_data'])) {
    header("Location: onetimeticket.php");
    exit();
}

$data = $_SESSION['ticket_data'];
$total_price = 0;

// Define fixed prices (as a fallback in case database query fails)
$prices = [
    'Adult' => 30.50,
    'Child' => 25.50,
    'Senior' => 25.50,
    'Infant' => 0.00
];

// Try to fetch prices from database
$sql = "SELECT ticket_type, price FROM type_of_ticket";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $prices[$row['ticket_type']] = $row['price'];
    }
}

// Validate data array
$data = array_merge([
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'sex' => '',
    'dob' => '',
    'tickets' => []
], $data);

// Ensure tickets array exists and has all types
$data['tickets'] = array_merge([
    'Adult' => 0,
    'Child' => 0,
    'Senior' => 0,
    'Infant' => 0
], is_array($data['tickets']) ? $data['tickets'] : []);

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary - Zoo Tickets</title>
    <link rel="stylesheet" href="../assets/css/ticket_summary.css">
</head>

<div class="container">
    <?php include('../includes/navbar.php'); ?>
   
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']); // Clear the error message
            ?>
        </div>
    <?php endif; ?>
    
    <div class="summary-container">
        <h1 class="summary-title">Order Summary</h1>
        
        <div class="summary-section">
            <h2>Personal Information</h2>
            <div class="summary-row">
                <span>First Name:</span>
                <span><?php echo htmlspecialchars($data['first_name']); ?></span>
            </div>
            <div class="summary-row">
                <span>Last Name:</span>
                <span><?php echo htmlspecialchars($data['last_name']); ?></span>
            </div>
            <div class="summary-row">
                <span>Email:</span>
                <span><?php echo htmlspecialchars($data['email']); ?></span>
            </div>
            <div class="summary-row">
                <span>Gender:</span>
                <span><?php echo $data['sex'] === 'M' ? 'Male' : 'Female'; ?></span>
            </div>
            <div class="summary-row">
                <span>Date of Birth:</span>
                <span><?php echo htmlspecialchars($data['dob']); ?></span>
            </div>
            <div class="summary-row">
                <span>Reservation Date:</span>
                <span><?php echo date('F j, Y', strtotime($data['reservation_date'])); ?></span>
            </div>
        </div>
        
        <div class="summary-section">
            <h2>Tickets Selected</h2>
            <?php
            foreach ($prices as $type => $price) {
                $quantity = isset($data['tickets'][$type]) ? (int)$data['tickets'][$type] : 0;
                if ($quantity > 0) {
                    $subtotal = $price * $quantity;
                    $total_price += $subtotal;
                    echo "<div class='summary-row'>
                            <span>$type x $quantity</span>
                            <span>$" . number_format($subtotal, 2) . "</span>
                          </div>";
                }
            }
            ?>
            <div class="total-price">
                Total Price: $<?php echo number_format($total_price, 2); ?>
            </div>
        </div>
        
        <div class="action-buttons">
            <form action="onetimeticket.php" method="GET" style="display: inline;">
                <input type="hidden" name="edit" value="1">
                <button type="submit" class="action-button edit-button">Edit</button>
            </form>
            <form action="process_ticket.php" method="POST" style="display: inline;">
                <button type="submit" class="action-button complete-button">Complete Purchase</button>
            </form>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
</div>