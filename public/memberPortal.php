<?php
// Start the session
include '../config/database.php';
include '../scripts/authorize.php';
?>

<head>
    <link rel="stylesheet" href="../assets/css/memberPortal.css">
</head>
<?php include('../includes/navbar.php'); ?>
<?php
if (!$is_member) {
    echo "<script>
        alert('Please log in as a member to access!');
        setTimeout(function() {
            location.href = 'login.php';
        }, 1000); // 3-second delay before redirecting
    </script>";
    exit();
}

$query = "SELECT 
    customers.first_name, 
    members.membership_type, 
    members.membership_status,
    members.membership_end_date,
    members.reward_points,
    COUNT(DISTINCT tickets.transaction_date) AS total_visits, 
    COUNT(tickets.transaction_number) AS total_tickets_purchased
FROM customers
JOIN members ON customers.cust_id = members.member_id
LEFT JOIN tickets ON tickets.cust_id = customers.cust_id
WHERE members.member_id = ?
GROUP BY customers.first_name, members.membership_type, members.membership_status,members.membership_end_date,members.reward_points";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id); // Assuming $user_id is the member's ID
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt->close();
?>
<div class="container">
    <div class="tabs">
        <h1 class="member-title">Membership Portal</h1>
        <div class="tab active" onclick="showTab(1)">Dashboard</div>
        <div class="tab" onclick="showTab(2)">Membership</div>
        <div class="tab" onclick="showTab(3)">Tickets</div>
        <div class="tab" onclick="showTab(4)">Profile</div>
    </div>
    <div class="change-container" id="change-container">
        <div class="tab-content active" id="1">
            <h1 class="welcome">Welcome back,
                <?php echo $row['first_name'] ?>
            </h1>
            <div class="info">
                <div>
                    <h1 class="fact">Membership Type:
                        <?php echo $row['membership_type'] ?>
                    </h1>
                    <h1 class="fact">Membership Status:
                        <?php echo $row['membership_status'] ?>
                    </h1>
                    <h1 class="fact">Membership Expiration:
                        <?php echo $row['membership_end_date'] ?>
                    </h1>
                    <h1 class="fact">Total visits:
                        <?php echo $row['total_visits'] ?>
                    </h1>
                    <h1 class="fact">Total tickets purchased:
                        <?php echo $row['total_tickets_purchased'] ?>
                    </h1>
                    <h1 class="Rewards">Reward points:
                        <?php echo $row['reward_points'] ?>
                    </h1>
                </div>
                <div class="Rewards-info">
                        <h2>Reward Points explained!</h2>
                        Zoo Rewards Points are our way of saying thank you for being a valued member! Each point is worth one cent and can be used to discount the price of your tickets. You can earn points in several ways:
                        <h3>By registering as a member: 500 points</h3>
                        <h3>Purchasing tickets: 100 points</h3>
                        <h3>Shopping at our gift shop: 50 points for every dollar you spend</h3>
                    </div>
            </div>
        </div>
        <div class="tab-content" id="2">
            <div class="renew-membership">
                <h1 class="welcome">Membership</h1>
                <h2>Renew your membership:</h2>

                <form method="POST" action="../scripts/renew_membership.php">
                    <div class="form-group">
                        <label for="membership">Choose Membership Type</label>
                        <select id="membership" name="membership_type" onchange="changeContent()" required>
                            <option value="standard">Standard</option>
                            <option value="family">Family</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>

                    <div id="membershipInfo">
                        <h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a discounted price. Applies to one person</h3>
                        <div class='renew-img'><img class="renew-ticket" src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>
                    </div>

                    <button type="submit" class="renew-btn">Renew Membership</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function showTab(step) {
        const tabContents = document.querySelectorAll('.tab-content');
        const tabs = document.querySelectorAll('.tab');
        tabContents.forEach(content => content.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));

        if (step != 5) {
            document.getElementById(step).classList.add('active');
            tabs[step - 1].classList.add('active');
        } else if (step === 5) {
            document.getElementById(step).classList.add('active');
        }
    }

    function changeContent() {
        var select = document.getElementById("membership");
        var div = document.getElementById("membershipInfo");
        var selectedOption = select.value;

        if (selectedOption === "standard") {
            div.innerHTML = "<h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a discounted price. Applies to one person</h3> <div class='renew-img'><img class = 'renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>";
        } else if (selectedOption === "family") {
            div.innerHTML = "<h3>Perfect for families! This membership includes two adults and up to three children, offering a cost-effective way to enjoy the zoo together.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300' height='500'> <img class='adult' src='../assets/img/adult.png' alt='adult' height='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        } else if (selectedOption === "vip") {
            div.innerHTML = "<h3>Experience the zoo like never before! VIP members get unlimited entry, access to exclusive events, behind-the-scenes tours, and discounts on tickets, food, and gift shop purchases. </h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300' height='500'> <img class='adult' src='../assets/img/adult.png' alt='adult' height='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        }
    }
</script>