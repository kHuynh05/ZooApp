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
    // Insert customer data
    $email = $data['email'];
    $query = "SELECT cust_id FROM customers WHERE cust_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email); // Bind the email parameter as a string
    $stmt->execute();
    $stmt->bind_result($cust_id); // Get the count result
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
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    $reservation_date = $data['reservation_date'];

    // Validate ticket types before insertion
    $valid_ticket_types = ['Adult', 'Child', 'Senior', 'Infant'];

    // Insert ticket records
    $ticket_stmt = $conn->prepare("INSERT INTO tickets (transaction_date, transaction_time, cust_id, ticket_type, reservation_date) VALUES (?, ?, ?, ?, ?)");
    if (!$ticket_stmt) {
        throw new Exception("Prepare failed for tickets: " . $conn->error);
    }

    foreach ($data['tickets'] as $type => $quantity) {
        // Validate ticket type
        if (!in_array($type, $valid_ticket_types)) {
            throw new Exception("Invalid ticket type: " . $type);
        }

        if ($quantity > 0) {  // Only process if quantity is greater than 0
            for ($i = 0; $i < $quantity; $i++) {
                $ticket_stmt->bind_param(
                    "ssiss",
                    $current_date,
                    $current_time,
                    $cust_id,
                    $type,
                    $reservation_date
                );

                if (!$ticket_stmt->execute()) {
                    throw new Exception("Ticket insert failed: " . $ticket_stmt->error . " for type: " . $type);
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
