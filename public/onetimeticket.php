<?php
// Include database connection
include '../config/database.php';

// Initialize variables for form data
$first_name = $last_name = $email = $dob = $sex = '';
$ticket_counts = [
    'Adult' => 0,
    'Child' => 0,
    'Senior' => 0,
    'Infant' => 0
];

// If coming back to edit
if (isset($_GET['edit']) && isset($_SESSION['ticket_data'])) {
    $data = $_SESSION['ticket_data'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $dob = $data['dob'];
    $sex = $data['sex'];
    $ticket_counts = isset($data['tickets']) ? $data['tickets'] : $ticket_counts;
}

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store form data in session for persistence between edit and summary
    $_SESSION['ticket_data'] = $_POST;
    
    // Redirect to summary page
    header("Location: ticket_summary.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Tickets - General Admission</title>
    <link rel="stylesheet" href="../assets/css/onetimeticket.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <?php include('../includes/navbar.php'); ?>
        
        <div class="content-wrapper">
            <div class="ticket-section">
                <h1 class="page-title">General Admission</h1>
                
                <form method="POST" id="ticketForm" class="ticket-form">
                    <div class="personal-info">
                        <h2>Personal Information</h2>
                        
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sex">Gender</label>
                            <select id="sex" name="sex" required>
                                <option value="">Select Gender</option>
                                <option value="M" <?php echo $sex === 'M' ? 'selected' : ''; ?>>Male</option>
                                <option value="F" <?php echo $sex === 'F' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required 
                                   max="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="ticket-transaction">
                        <h2>Select Tickets</h2>
                        <div class="ticket-categories">
                            <?php
                            $tickets_info = [
                                'Adult' => ['price' => 30.50, 'age_range' => 'Ages 12-64'],
                                'Child' => ['price' => 25.50, 'age_range' => 'Ages 3-12'],
                                'Senior' => ['price' => 25.50, 'age_range' => 'Ages 65+'],
                                'Infant' => ['price' => 0.00, 'age_range' => 'Ages 2 & Under']
                            ];
                            
                            foreach ($tickets_info as $type => $info) {
                                $current_count = isset($ticket_counts[$type]) ? $ticket_counts[$type] : 0;
                                echo "
                                <div class='ticket-type'>
                                    <div class='ticket-info'>
                                        <div class='ticket-details'>
                                            <span class='ticket-label'>$type</span>
                                            <span class='age-range'>({$info['age_range']})</span>
                                        </div>
                                        <span class='ticket-price'>$" . number_format($info['price'], 2) . "</span>
                                    </div>
                                    <div class='quantity-control'>
                                        <button type='button' class='quantity-btn minus' onclick='updateQuantity(\"$type\", -1)'>
                                            <i class='fas fa-minus'></i>
                                        </button>
                                        <span class='quantity' id='" . $type . "_quantity'>" . $current_count . "</span>
                                        <input type='hidden' name='tickets[" . $type . "]' id='" . $type . "_input' value='" . $current_count . "'>
                                        <button type='button' class='quantity-btn plus' onclick='updateQuantity(\"$type\", 1)'>
                                            <i class='fas fa-plus'></i>
                                        </button>
                                    </div>
                                </div>";
                            }
                            ?>
                        </div>

                        <div class="reservation-date">
                            <h2>Select Visit Date</h2>
                            <div class="form-group">
                                <label for="reservation_date">Reservation Date</label>
                                <input type="date" id="reservation_date" name="reservation_date" required
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       max="<?php echo date('Y-m-d', strtotime('+6 months')); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="buy-button">Buy Tickets</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include('../includes/footer.php'); ?>
    </div>

    <script>
    function updateQuantity(type, change) {
        const quantitySpan = document.getElementById(type + '_quantity');
        const quantityInput = document.getElementById(type + '_input');
        let currentQuantity = parseInt(quantitySpan.textContent);
        
        // Calculate new quantity
        let newQuantity = currentQuantity + change;
        
        // Enforce limits
        if (newQuantity >= 0 && newQuantity <= 20) {
            quantitySpan.textContent = newQuantity;
            quantityInput.value = newQuantity;
        }
    }

    // Initialize ticket quantities from saved values
    window.onload = function() {
        <?php foreach ($ticket_counts as $type => $count) { ?>
            document.getElementById('<?php echo $type; ?>_quantity').textContent = '<?php echo $count; ?>';
            document.getElementById('<?php echo $type; ?>_input').value = '<?php echo $count; ?>';
        <?php } ?>
    };
    </script>
</body>
</html>