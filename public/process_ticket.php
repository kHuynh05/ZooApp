<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['ticket_data'])) {
    header("Location: onetimeticket.php");
    exit();
}

$data = $_SESSION['ticket_data'];

// Start transaction
$conn->begin_transaction();

try {
    // First, check if the selected date is in the past
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
    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, cust_email, date_of_birth, sex) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssss", 
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['dob'],
        $data['sex']
    );
    
    if (!$stmt->execute()) {
        // Check for trigger errors
        if ($stmt->errno == 1644) { // SQL State '45000' custom error
            throw new Exception($stmt->error);
        }
        throw new Exception("Customer insert failed: " . $stmt->error);
    }
    
    $cust_id = $conn->insert_id;
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Insert ticket records
    $ticket_stmt = $conn->prepare("INSERT INTO tickets (transaction_date, transaction_time, cust_id, ticket_type, reservation_date) VALUES (?, ?, ?, ?, ?)");
    if (!$ticket_stmt) {
        throw new Exception("Prepare failed for tickets: " . $conn->error);
    }
    
    foreach ($data['tickets'] as $type => $quantity) {
        if ($quantity > 0) {
            for ($i = 0; $i < $quantity; $i++) {
                $ticket_stmt->bind_param("ssiss",
                    $current_date,
                    $current_time,
                    $cust_id,
                    $type,
                    $reservation_date
                );
                
                if (!$ticket_stmt->execute()) {
                    // Check for trigger errors
                    if ($ticket_stmt->errno == 1644) { // SQL State '45000' custom error
                        throw new Exception($ticket_stmt->error);
                    }
                    throw new Exception("Ticket insert failed: " . $ticket_stmt->error);
                }
            }
        }
    }
    
    // If we got here, everything worked
    $conn->commit();
    
    // Store transaction data for receipt
    $_SESSION['transaction_data'] = [
        'cust_id' => $cust_id,
        'tickets' => $data['tickets'],
        'reservation_date' => $reservation_date
    ];
    
    // Clear the ticket data session
    unset($_SESSION['ticket_data']);
    
    // Redirect to receipt page
    header("Location: ticket_receipt.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Store error message in session with user-friendly message
    $error_message = $e->getMessage();
    if (strpos($error_message, "Cannot sell a ticket for a past reservation date") !== false) {
        $_SESSION['error_message'] = "Sorry, tickets cannot be sold for past dates. Please select a future date.";
    } else if (strpos($error_message, "Daily ticket limit reached") !== false) {
        $_SESSION['error_message'] = "Sorry, we have reached our daily ticket limit for the selected date. Please choose another date.";
    } else {
        $_SESSION['error_message'] = "Error processing transaction. Please try again.";
    }
    
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
?>