<?php
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
        }, 1000);
    </script>";
    exit();
}

$query = "SELECT 
    customers.first_name, 
    customers.last_name,
    customers.cust_email,
    members.password,
    members.membership_type, 
    members.membership_status,
    members.membership_start_date,
    members.membership_end_date,
    members.reward_points,
    COALESCE(COUNT(DISTINCT tickets.reservation_date), 0) AS total_visits, 
    COALESCE(COUNT(tickets.ticket_id), 0) AS total_tickets_purchased
FROM customers
JOIN members ON customers.cust_id = members.member_id
LEFT JOIN transactions ON transactions.cust_id = customers.cust_id
LEFT JOIN tickets ON tickets.transaction_number = transactions.transaction_number
WHERE members.member_id = ?
GROUP BY customers.cust_id, 
         customers.first_name, 
         customers.last_name,
         customers.cust_email,
         members.password,
         members.membership_type, 
         members.membership_status, 
         members.membership_start_date,
         members.membership_end_date, 
         members.reward_points;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt->close();

$conn->close();

?>
<div class="container">
    <div class="tabs">
        <h1 class="member-title">Membership Portal</h1>
        <div class="tab active" onclick="showTab(1)">Dashboard</div>
        <div class="tab" onclick="showTab(2)">Membership</div>
        <div class="tab" onclick="showTab(3)">Recent Orders</div>
        <div class="tab" onclick="showTab(4)">Profile</div>
    </div>
    <div class="change-container" id="change-container">
        <span id="error-message" class="error-message" style="display: none; padding: 10px; color: white;"></span>

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
                    <h1 class="fact">Membership Since:
                        <?php echo $row['membership_start_date'] ?>
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
            <a href="ticket.php">
                <button class="ticket-header">Buy Tickets</button>
            </a>
        </div>
        <div class="tab-content" id="2">
            <div class="renew-membership">
                <h1 class="welcome">Membership</h1>
                <h2>Renew your membership:</h2>

                <div class="form-group">
                    <label for="membership">Choose Membership Type</label>
                    <select id="membership" name="membership_type" onchange="changeContent()" required>
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                        <option value="Vip">VIP</option>
                        <option value="Free">Free</option>
                    </select>
                </div>

                <div id="membershipInfo">
                    <h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a 15% discounted price.</h3>
                    <div class='renew-img'><img class="renew-ticket" src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>
                </div>

                <button class="renew-btn" onclick="passMembershipInfo(); showTab(5);">Renew Membership</button>
            </div>
        </div>
        <div class="tab-content" id="3">
            <h1 class="welcome">Recent Orders</h1>
            <div class="filters">
                <div class="filters">
                    <span>Orders placed within *</span>
                    <select name="time" id="time" onchange="update()">
                        <option value="1_month">1 month</option>
                        <option value="3_months">3 months</option>
                        <option value="6_months">6 months</option>
                        <option value="1_year">1 year</option>
                    </select>
                </div>
                <div class="filters">
                    <span>Type of Orders</span>
                    <select name="type" id="type" onchange="update()">
                        <option value="registration">registration</option>
                        <option value="tickets">tickets</option>
                        <option value="donations">donations</option>
                        <option value="shop">shop</option>
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction Number</th>
                            <th>Transaction Date</th>
                            <th>Transaction Time</th>
                            <th>Transaction Type</th>
                            <th>Total Profit</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                    </tbody>
                </table>
            </div>

        </div>
        <div class="tab-content" id="4">
            <form id="profileForm">
                <h1 class="welcome">Edit Profile</h1>
                <label>Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>"><br><br>

                <label>Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>"><br><br>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($row['cust_email']) ?>"><br><br>

                <label>Old Password:</label>
                <input type="password" name="oldPassword" value=""><br><br>

                <label>New Password:</label>
                <input type="password" name="newPassword" value=""><br><br>

                <label>Verify Password:</label>
                <input type="password" name="verifyPassword" value=""><br><br>

                <button class="renew-btn" type="button" onclick="saveProfile()">Save</button>
            </form>
        </div>
        <div class="tab-content" id="5">
            <h1 class="Payment">Payment:</h1>
            <div class="payment-summary">
                <h3>Membership Type:
                    <span id="membership-type"><?php echo $row['membership_type']; ?></span>
                </h3>
                <h3>Renewal Status:
                    <span id="renewal-status">
                        <?php
                        $current_date = new DateTime();
                        $end_date = new DateTime($row['membership_end_date']);
                        if ($current_date <= $end_date) {
                            echo "On time (25% Discount applied)";
                        } else {
                            echo "Late Renewal (No Discount)";
                        }
                        ?>
                    </span>
                </h3>
                <h3>Amount:
                    <span id="payment-amount">
                        <?php
                        $basePrices = array(
                            "Standard" => 70,
                            "Family" => 120,
                            "Vip" => 150
                        );
                        $discount = ($current_date <= $end_date) ? 0.25 : 0;

                        $amount = $basePrices[$row['membership_type']] * (1 - $discount);
                        echo "$" . number_format($amount, 2);
                        ?>
                    </span>
                </h3>
            </div>

            <div class="membership-info">
                <h3>Membership Summary:</h3>
                <p>Renew on time for a 25% discount on the renewal price.</p>
                <p>Your membership will expire on: <span id="membership-expiry"><?php echo $row['membership_end_date']; ?></span></p>
            </div>

            <form id="membership_form" action="../scripts/renew_membership.php" method="POST">
                <input type="hidden" name="membership_type" id="membership_type_input">
                <button class="renew-btn" type="button" onclick="passMembershipInfoToForm()">Renew Membership</button>
            </form>
        </div>
    </div>
</div>
<script>
    function saveProfile() {
        let form = document.getElementById("profileForm");
        if (!form) {
            console.error("Form not found!");
            return;
        }

        let formData = new FormData(form);

        let oldPassword = formData.get("oldPassword")?.trim() || "";
        let newPassword = formData.get("newPassword")?.trim() || "";
        let verifyPassword = formData.get("verifyPassword")?.trim() || "";

        let firstName = formData.get("first_name")?.trim() || "";
        let lastName = formData.get("last_name")?.trim() || "";
        let email = formData.get("email")?.trim() || "";

        let profileUpdateData = {};
        if (firstName) profileUpdateData.first_name = firstName;
        if (lastName) profileUpdateData.last_name = lastName;
        if (email) profileUpdateData.email = email;

        let passwordUpdateData = {};
        if (oldPassword && newPassword && verifyPassword) {
            if (newPassword !== verifyPassword) {
                showError("New password and verify password do not match.");
                return;
            }
            passwordUpdateData = {
                oldPassword: oldPassword,
                newPassword: newPassword
            };
        } else if (newPassword && verifyPassword && !oldPassword) {
            showError("Enter your old password");
            return;
        }

        // Prepare final data to send
        let dataToSend = {
            profile: profileUpdateData,
            password: passwordUpdateData
        };

        fetch("../scripts/update_profile.php", {
                method: "POST",
                body: JSON.stringify(dataToSend),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.error);
                } else {
                    showError(data.error || "Error occurred while updating the profile.");
                }
            })
            .catch(error => {
                showError(error.message || "Error occurred while processing the request.");
            });
    }

    // Function to display success message
    function showSuccess(message) {
        const errorMessageDiv = document.getElementById("error-message");
        errorMessageDiv.innerText = message;
        errorMessageDiv.style.backgroundColor = "#dfd";
        errorMessageDiv.style.color = "green"; // Optional: Make text white for better contrast
        errorMessageDiv.style.display = "block";
        setTimeout(() => {
            errorMessageDiv.style.display = "none";
        }, 3000);
    }

    // Function to display error message
    function showError(message) {
        const errorMessageDiv = document.getElementById("error-message");
        errorMessageDiv.innerText = message;
        errorMessageDiv.style.backgroundColor = "#fee";
        errorMessageDiv.style.color = "red";
        errorMessageDiv.style.display = "block";
        setTimeout(() => {
            errorMessageDiv.style.display = "none";
        }, 3000);
    }

    function showTab(step) {
        const tabContents = document.querySelectorAll('.tab-content');
        const tabs = document.querySelectorAll('.tab');
        tabContents.forEach(content => content.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));

        if (step !== 5) {
            document.getElementById(step).classList.add('active');
            tabs[step - 1].classList.add('active');
        } else if (step === 5) {
            document.getElementById(step).classList.add('active');
            var membershipType = sessionStorage.getItem('membershipType');
            console.log("Selected Membership Type: " + membershipType);

            if (membershipType) {
                document.getElementById('membership-type').textContent = membershipType;
                updatePaymentAmount(membershipType);
            } else {
                console.log("No membership type found in sessionStorage");
            }
        }
    }

    function changeContent() {
        var select = document.getElementById("membership");
        var div = document.getElementById("membershipInfo");
        var selectedOption = select.value;

        if (selectedOption === "Free") {
            div.innerHTML = "<h3>Basic membership with access to purchase tickets at regular prices. No discounts or reward points available.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'></div>";
        } else if (selectedOption === "Standard") {
            div.innerHTML = "<h3>Enjoy general admission to the zoo during regular hours, giving you access to all exhibits and daily shows for a 15% discounted price. Applies to one person</h3> <div class='renew-img'><img class = 'renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img src='../assets/img/adult.png' alt='adult' width='300'></div>";
        } else if (selectedOption === "Premium") {
            div.innerHTML = "<h3>Perfect for families! This membership includes a 25% discount, offering a cost-effective way to enjoy the zoo together.</h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300' height='500'> <img class='adult' src='../assets/img/adult.png' alt='adult' height='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        } else if (selectedOption === "Vip") {
            div.innerHTML = "<h3>Experience the zoo like never before! VIP members get a 40% discount, access to exclusive events, behind-the-scenes tours, and discounts on tickets, food, and gift shop purchases. </h3> <div class='renew-img'><img class='renew-ticket' src='../assets/img/ticket.png' alt='Ticket' width='400'><img class='adult' src='../assets/img/adult.png' alt='adult' width='300' height='500'> <img class='adult' src='../assets/img/adult.png' alt='adult' height='300'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'> <img class='child' src='../assets/img/child.png' alt='child' width='100'></div>";
        }
    }

    function passMembershipInfoToForm() {
        var membershipType = sessionStorage.getItem('membershipType');

        if (membershipType) {
            document.getElementById('membership_type_input').value = membershipType;
            document.getElementById('membership_form').submit();
        } else {
            console.log("No membership_type found in sessionStorage.");
        }
    }

    function passMembershipInfo() {
        var selectedMembership = document.getElementById('membership').value;

        sessionStorage.setItem('membershipType', selectedMembership);

        showTab(5);
    }

    function updatePaymentAmount(membershipType) {
        const basePrices = {
            Free: 0,
            Standard: 70,
            Premium: 120,
            Vip: 150
        };

        var discount = membershipType === 'Free' ? 0 : 0.25;
        var price = basePrices[membershipType] * (1 - discount);

        document.getElementById('payment-amount').textContent = "$" + price.toFixed(2);
    }

    function update() {
        let time = document.getElementById("time").value;
        let orderType = document.getElementById("type").value;
        let ordersTableBody = document.getElementById("ordersTableBody");

        let dataToSend = {
            time: time,
            orderType: orderType
        };

        fetch("../scripts/update_Orders.php", {
                method: "POST",
                body: JSON.stringify(dataToSend),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the existing table content
                    ordersTableBody.innerHTML = "";

                    // Check if orders data is available and is an array
                    if (Array.isArray(data.transactions) && data.transactions.length > 0) {
                        data.transactions.forEach(transaction => {
                            // Add a new row to the table
                            let row = `
                        <tr>
                            <td>${transaction.transaction_number}</td>
                            <td>${transaction.transaction_date}</td>
                            <td>${transaction.transaction_time}</td>
                            <td>${transaction.transaction_type}</td>
                            <td>${transaction.total_profit}</td>
                        </tr>
                    `;
                            ordersTableBody.innerHTML += row;
                        });
                    } else {
                        // If no orders are found, display this message
                        ordersTableBody.innerHTML = "<tr><td colspan='5'>No transactions found.</td></tr>";
                    }
                } else {
                    showError(data.error || "Error occurred while fetching orders.");
                }
            })
            .catch(error => {
                showError(error.message || "Error occurred while processing the request.");
            });
    }
</script>