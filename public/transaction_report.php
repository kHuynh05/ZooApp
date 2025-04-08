<?php
include '../config/database.php';
include '../scripts/employeeRole.php';

// Handle AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    error_log('Received AJAX request');
    error_log('POST data: ' . file_get_contents('php://input'));
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Get filter values and sanitize them
        $start_date = !empty($data['start_date']) ? $conn->real_escape_string($data['start_date']) : null;
        $end_date = !empty($data['end_date']) ? $conn->real_escape_string($data['end_date']) : null;
        $type_filter = isset($data['type']) ? $conn->real_escape_string($data['type']) : 'all';

        $transactions = [];
        $summary = [
            'registration' => 0.00,
            'tickets' => 0.00,
            'shop' => 0.00,
            'donation' => 0.00
        ];

        // --- Build WHERE clause for dates ---
        $date_condition = "";
        if ($start_date) {
            // Assuming transaction_date is DATETIME or TIMESTAMP
            $date_condition .= " AND DATE(transaction_date) >= '$start_date'";
        }
        if ($end_date) {
            // Assuming transaction_date is DATETIME or TIMESTAMP
            $date_condition .= " AND DATE(transaction_date) <= '$end_date'";
        }

        // --- Query for regular transactions and calculate sums ---
        $allowed_types = ['registration', 'tickets', 'shop'];
        foreach ($allowed_types as $current_type) {
            // Fetch individual transactions if needed (only if 'all' or matching type filter is selected)
            if ($type_filter === 'all' || $type_filter === $current_type) {
                $trans_query = "SELECT
                    transaction_number,
                    DATE(transaction_date) as date,
                    transaction_time as time,
                    total_profit as amount,
                    transaction_type as type
                    FROM transactions WHERE transaction_type = '$current_type'" . $date_condition;

                $result = $conn->query($trans_query);
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $transactions[] = [
                            'transaction_number' => $row['transaction_number'],
                            'date' => $row['date'],
                            'time' => $row['time'],
                            // Ensure amount is correctly formatted
                            'amount' => number_format((float)$row['amount'], 2),
                            'type' => $row['type']
                        ];
                    }
                } else {
                     error_log("Error fetching $current_type transactions: " . $conn->error);
                }
            }

            // Calculate sum for this type, applying date condition
            $sum_query = "SELECT SUM(total_profit) as total FROM transactions WHERE transaction_type = '$current_type'" . $date_condition;
            $sum_result = $conn->query($sum_query);
            if ($sum_result && $sum_row = $sum_result->fetch_assoc()) {
                 $summary[$current_type] = $sum_row['total'] ?? 0.00;
            } else {
                 error_log("Error calculating $current_type sum: " . $conn->error);
            }
        }

        // --- Query for donations and calculate sum ---
        if ($type_filter === 'all' || $type_filter === 'donation') {
            // Fetch individual donations if needed
            $don_query = "SELECT
                transaction_number,
                DATE(transaction_date) as date,
                TIME(transaction_date) as time,
                donation_amount as amount,
                transaction_type as type
                FROM donations
                WHERE transaction_type = 'donation'" . $date_condition; // Apply date condition here too

            $result = $conn->query($don_query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $transactions[] = [
                        'transaction_number' => 'D' . $row['transaction_number'],
                        'date' => $row['date'],
                        'time' => $row['time'],
                         // Ensure amount is correctly formatted
                        'amount' => number_format((float)$row['amount'], 2),
                        'type' => 'donation' // Explicitly set type
                    ];
                }
            } else {
                error_log("Error fetching donations: " . $conn->error);
            }

            // Calculate donation sum, applying date condition
            $don_sum_query = "SELECT SUM(donation_amount) as total FROM donations WHERE transaction_type = 'donation'" . $date_condition;
            $don_sum_result = $conn->query($don_sum_query);
             if ($don_sum_result && $don_sum_row = $don_sum_result->fetch_assoc()) {
                 $summary['donation'] = $don_sum_row['total'] ?? 0.00;
            } else {
                 error_log("Error calculating donation sum: " . $conn->error);
            }
        }

        // Format summary values
        foreach ($summary as $key => $value) {
            $summary[$key] = number_format((float)$value, 2);
        }

        // Send the combined response
        header('Content-Type: application/json');
        // Sort transactions by date and time if needed before sending
         usort($transactions, function($a, $b) {
             $datetimeA = strtotime($a['date'] . ' ' . $a['time']);
             $datetimeB = strtotime($b['date'] . ' ' . $b['time']);
             return $datetimeA <=> $datetimeB; // Sort ascending
         });
        echo json_encode(['transactions' => $transactions, 'summary' => $summary]);
        exit;

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
?>

<div id="transactionReport">
    <head>
        <link rel="stylesheet" href="../assets/css/transaction_report.css">
    </head>

    <h1>Transaction Report</h1>

    <div class="filter-container">
        <div class="date-filters">
            <div class="filter-group">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date">
            </div>
            <div class="filter-group">
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date">
            </div>
            <div class="filter-group">
                <label for="type">Type:</label>
                <select id="type" name="type">
                    <option value="all">All</option>
                    <option value="registration">Registration</option>
                    <option value="tickets">Tickets</option>
                    <option value="shop">Shop</option>
                    <option value="donation">Donation</option>
                </select>
            </div>
            <button onclick="filterTransactions()">Filter</button>
        </div>
        <!-- Summary container -->
        <div class="summary-container">
             <div class="summary-box">
                <h4>Registrations</h4>
                <p id="total_registration">$0.00</p>
            </div>
             <div class="summary-box">
                <h4>Tickets</h4>
                <p id="total_tickets">$0.00</p>
            </div>
            <div class="summary-box">
                <h4>Shop</h4>
                <p id="total_shop">$0.00</p>
            </div>
             <div class="summary-box">
                <h4>Donations</h4>
                <p id="total_donation">$0.00</p>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Transaction Number</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Amount</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody id="transactionTableBody">
                 <!-- Rows will be loaded here by JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        // Initial load of transactions
        document.addEventListener('DOMContentLoaded', function() {
            filterTransactions();
        });

        function filterTransactions() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const type = document.getElementById('type').value;

            // Reset summary fields and show loading
            document.getElementById('total_registration').textContent = 'Loading...';
            document.getElementById('total_tickets').textContent = 'Loading...';
            document.getElementById('total_shop').textContent = 'Loading...';
            document.getElementById('total_donation').textContent = 'Loading...';

            // Add loading indicator to table
            const tableBody = document.getElementById('transactionTableBody');
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';

            // Make the fetch request
            fetch('transaction_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    type: type
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text(); // Get the raw text first
            })
            .then(text => {
                try {
                    // Try to parse the JSON
                    const data = JSON.parse(text);

                    // Clear the table body
                    tableBody.innerHTML = '';

                    // Check if we got an error message from PHP
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // --- Update Summary ---
                    const summary = data.summary || {}; // Default to empty object if summary is missing
                    document.getElementById('total_registration').textContent = '$' + (summary.registration || '0.00');
                    document.getElementById('total_tickets').textContent = '$' + (summary.tickets || '0.00');
                    document.getElementById('total_shop').textContent = '$' + (summary.shop || '0.00');
                    document.getElementById('total_donation').textContent = '$' + (summary.donation || '0.00');

                    // --- Update Table ---
                    const transactions = data.transactions || []; // Default to empty array if transactions missing
                    if (!Array.isArray(transactions) || transactions.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No transactions found for the selected criteria.</td></tr>';
                        return;
                    }

                    // Display the data in the table
                    transactions.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${transaction.transaction_number || ''}</td>
                            <td>${transaction.date || ''}</td>
                            <td>${transaction.time || ''}</td>
                            <td>${transaction.amount || '0.00'}</td>
                            <td>${transaction.type || ''}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                } catch (e) {
                    console.error('JSON Parse or Processing Error:', e);
                    console.log('Raw server response:', text); // Log raw text for debugging
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error processing server response.</td></tr>';
                     // Reset summary fields on processing error
                    document.getElementById('total_registration').textContent = '$0.00';
                    document.getElementById('total_tickets').textContent = '$0.00';
                    document.getElementById('total_shop').textContent = '$0.00';
                    document.getElementById('total_donation').textContent = '$0.00';
                    // Optionally display the error message: throw new Error('Failed to parse server response');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: red;">
                            Error loading transactions. Please check connection or server logs.
                        </td>
                    </tr>`;
                 // Reset summary fields on fetch error
                document.getElementById('total_registration').textContent = '$0.00';
                document.getElementById('total_tickets').textContent = '$0.00';
                document.getElementById('total_shop').textContent = '$0.00';
                document.getElementById('total_donation').textContent = '$0.00';
            });
        }
    </script>
</div>
