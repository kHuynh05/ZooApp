<?php
include '../config/database.php';
include '../scripts/authorize.php';

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

$query = "SELECT cust_id, first_name, last_name, cust_email, sex, date_of_birth FROM customers WHERE cust_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $data['email']);
$stmt->execute();
$stmt->bind_result($cust_id, $first_name, $last_name, $email, $sex, $dob);
$stmt->fetch();
$stmt->close();
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
                    $ticket_price = $price;
                    if ($is_member) {
                        $ticket_price = $price * (1 - $member_discount);
                    }
                    $subtotal = $ticket_price * $quantity;
                    $total_price += $subtotal;
                    echo "<div class='summary-row'>
                            <span>$type x $quantity</span>
                            <span>$" . number_format($subtotal, 2) . "</span>
                          </div>";
                }
            }
            ?>
            <?php
            if ($is_member) {
                echo "<div class='use-reward-points' style='display: flex; align-items: center; gap: 5px;'>
            <input type='checkbox' id='usePoints' name='usePoints' value='1' onchange='toggleRewardPoints()' style='width: 16px; height: 16px; appearance: auto;'>
            <label for='usePoints' style='margin: 0;'>Use Reward Points</label>
          </div> 
          <div id='rewardPointsSection' style='display: none; margin-top: 10px;'>
            <label for='pointsToUse'>Enter Points to Use: </label>
            <input type='number' id='pointsToUse' name='pointsToUse' min='0' max='" . $reward_points . "' value='0' oninput='updateTotalPrice()'>
            <span>Available Points: " . $reward_points . "</span>
          </div>";
            }
            ?>
            <div class="total-price">
                Total Price: <span id="totalPrice" data-original="<?php echo number_format($total_price, 2, '.', ''); ?>">
                    $<?php echo number_format($total_price, 2); ?>
                </span>
            </div>
        </div>

        <div class="action-buttons">
            <form action="onetimeticket.php" method="GET" style="display: inline;">
                <input type="hidden" name="edit" value="1">
                <button type="submit" class="action-button edit-button">Edit</button>
            </form>
            <form action="process_ticket.php" method="POST" style="display: inline;">
                <input type="hidden" id="hiddenPointsToUse" name="pointsToUse" value="0">
                <input type="hidden" id="finalTotalPrice" name="finalTotalPrice" value="0">
                <button type="submit" class="action-button complete-button">Complete Purchase</button>
            </form>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
</div>

<script>
    function toggleRewardPoints() {
        let checkbox = document.getElementById('usePoints');
        let pointsSection = document.getElementById('rewardPointsSection');

        if (checkbox.checked) {
            pointsSection.style.display = 'block';
        } else {
            pointsSection.style.display = 'none';
            document.getElementById('pointsToUse').value = 0;
            updateTotalPrice();
        }
    }

    function updateTotalPrice() {
        let baseTotal = parseFloat(document.getElementById('totalPrice').dataset.original);
        let pointsInput = document.getElementById('pointsToUse');
        let pointsToUse = parseInt(pointsInput.value) || 0;
        let maxPoints = parseInt(pointsInput.max);

        // Ensure points do not exceed the allowed maximum
        if (pointsToUse > maxPoints) {
            pointsToUse = maxPoints;
            pointsInput.value = maxPoints;
        }

        let pointsValue = pointsToUse * 0.01; // assuming 1 point = 0.01 for simplicity
        let newTotal = Math.max(baseTotal - pointsValue, 0);

        document.getElementById('totalPrice').textContent = '$' + newTotal.toFixed(2);
        document.getElementById('hiddenPointsToUse').value = pointsToUse; // Pass value to backend
        
        console.log("Points to use: " + document.getElementById('hiddenPointsToUse').value);
        console.log("Final total price: " + document.getElementById('finalTotalPrice').value);

        // Update the hidden field with the final price
        document.getElementById('finalTotalPrice').value = newTotal.toFixed(2); // Set the final price here
    }


    // Ensure total updates when the input changes
    document.getElementById('pointsToUse').addEventListener('input', updateTotalPrice);
</script>