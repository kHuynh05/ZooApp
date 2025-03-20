<?php
include '../config/database.php';
include '../scripts/authorize.php';

if (!isset($_SESSION['ticket_data'])) {
    $_SESSION['error_message'] = "No ticket data found. Please start over.";
    header("Location: ticket.php");
    exit();
}

// Get the data from session
$data = $_SESSION['ticket_data'];

// Get the points used and final total from POST data
if (isset($_POST['pointsToUse']) && isset($_POST['finalTotalPrice'])) {
    $points_used = (int)$_POST['pointsToUse'];
    $final_total = (float)$_POST['finalTotalPrice'];

    // Update the session data
    $_SESSION['ticket_data']['points_used'] = $points_used;
    $_SESSION['ticket_data']['final_total'] = $final_total;

    // Now the updated data is available in $data
    $data = $_SESSION['ticket_data'];
}

$conn->begin_transaction();

try {
    if (strtotime($data['reservation_date']) < strtotime(date('Y-m-d'))) {
        throw new Exception("Cannot sell a ticket for a past reservation date.");
    }

    // Check if adding these tickets would exceed the daily limit
    $reservation_date = $data['reservation_date'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tickets WHERE reservation_date = ?");
    $stmt->bind_param("s", $reservation_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_count = $result->fetch_assoc()['count'];

    // Calculate total tickets being purchased
    $total_new_tickets = array_sum($data['tickets']);

    if (($current_count + $total_new_tickets) > 30) {
        throw new Exception("Daily ticket limit reached. Cannot sell more tickets for this date.");
    }
    // Insert customer data
    if ($data['email'] != null) {
        $email = $data['email'];
        $query = "SELECT cust_id FROM customers WHERE cust_email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($cust_id);
        $stmt->fetch();
        $stmt->close();



        if ($cust_id == null) {
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, cust_email, date_of_birth, sex) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "sssss",
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['dob'],
                $data['sex']
            );

            if (!$stmt->execute()) {
                throw new Exception("Customer insert failed: " . $stmt->error);
            }
            $cust_id = $conn->insert_id;
        }
    }

    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Validate ticket types before insertion
    $valid_ticket_types = ['Adult', 'Child', 'Senior', 'Infant'];

    $ticket_stmt = $conn->prepare("INSERT INTO transactions (transaction_date, transaction_time, cust_id, total_profit, transaction_type) VALUES (?, ?, ?, ?,?)");
    if (!$ticket_stmt) {
        throw new Exception("Prepare failed for tickets: " . $conn->error);
    }

    $type = "tickets";
    $ticket_stmt->bind_param(
        "ssids",
        $current_date,
        $current_time,
        $cust_id,
        $_SESSION['ticket_data']['final_total'],
        $type
    );

    if (!$ticket_stmt->execute()) {
        throw new Exception("Transaction insert failed: " . $ticket_stmt->error . " for type: " . $type);
    }

    $transaction_id = $conn->insert_id;
    if (!$transaction_id) {
        throw new Exception("Failed to retrieve transaction ID.");
    }
    $ticket_stmt->close();

    $ticket_stmt = $conn->prepare("INSERT INTO tickets (transaction_number, ticket_type, reservation_date) VALUES (?, ?, ?)");
    if (!$ticket_stmt) {
        throw new Exception("Prepare failed for tickets: " . $conn->error);
    }
    foreach ($data['tickets'] as $type => $quantity) {
        // Validate ticket type
        if (!in_array($type, $valid_ticket_types)) {
            throw new Exception("Invalid ticket type: " . $type);
        }
        if ($quantity > 0) { // Only process if quantity is greater than 0
            for ($i = 0; $i < $quantity; $i++) {
                $ticket_stmt->bind_param(
                    "iss",
                    $transaction_id,
                    $type,
                    $reservation_date
                );


                if (!$ticket_stmt->execute()) {
                    throw new Exception("Transaction insert failed: " . $ticket_stmt->error . " for type: " . $type);
                }
            }
        }
    }
    $ticket_stmt->close();

    $ticket_stmt = $conn->prepare("SELECT reward_points FROM members WHERE member_id = ?");
    $ticket_stmt->bind_param("i", $cust_id);
    $ticket_stmt->execute();

    if (!$ticket_stmt->execute()) {
        throw new Exception("RewardPoints Read failed: " . $ticket_stmt->error);
    }
    $ticket_stmt->bind_result($reward_points);
    $ticket_stmt->fetch();
    $ticket_stmt->close();

    $totalTickets = array_sum($data['tickets']) - ($data['tickets']['Infant'] ?? 0);
    $totalPoints = $reward_points - $_SESSION['ticket_data']['points_used'] + (100 * $totalTickets);
    $ticket_stmt = $conn->prepare("UPDATE members SET reward_points = ? WHERE member_id = ?");
    $ticket_stmt->bind_param("ii", $totalPoints, $cust_id);

    if (!$ticket_stmt->execute()) {
        throw new Exception("RewardPoints udpated failed: " . $ticket_stmt->error);
    }

    $ticket_stmt->close();
    // If we got here, everything worked
    $conn->commit();

    // Store transaction data for receipt
    $_SESSION['transaction_data'] = [
        'transaction_number' => $transaction_id,
        'cust_id' => $cust_id,
        'tickets' => $data['tickets'],
        'reservation_date' => $reservation_date,
    ];

    // Clear the ticket data session
    unset($_SESSION['ticket_data']);

    // Redirect to receipt page
    header("Location: ticket_receipt.php");
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Store error message in session
    $_SESSION['error_message'] = "Error processing transaction: " . $e->getMessage();

    // Redirect back to summary page
    header("Location: ticket_summary.php");
    exit();
}

// Close prepared statements
if (isset($stmt)) {
    $stmt->close();
}
if (isset($ticket_stmt)) {
    $ticket_stmt->close();
}

// Close connection
$conn->close();