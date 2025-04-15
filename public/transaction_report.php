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
        // Initialize summary with count and amount fields
        $summary = [
            'registration_count' => 0, 'registration_amount' => 0.00,
            'tickets_count' => 0, 'tickets_amount' => 0.00,
            'shop_count' => 0, 'shop_amount' => 0.00,
            'donation_count' => 0, 'donation_amount' => 0.00
        ];

        // Determine which types to process based on the filter
        $types_to_process = [];
        if ($type_filter === 'all') {
            $types_to_process = ['registration', 'tickets', 'shop', 'donation'];
        } elseif (in_array($type_filter, ['registration', 'tickets', 'shop', 'donation'])) {
            $types_to_process[] = $type_filter; // Process only the selected type
        }

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

        // --- Query for regular transactions and calculate stats ---
        $allowed_types = ['registration', 'tickets', 'shop'];
        foreach ($allowed_types as $current_type) {
             // Skip if not the selected type (and not 'all')
             if (!in_array($current_type, $types_to_process)) {
                 continue;
             }

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
                    $result->free(); // Free result set
                } else {
                     error_log("Error fetching $current_type transactions: " . $conn->error);
                }
            }

            // Calculate sum and count for this type, applying date condition
             $stats_query = "SELECT SUM(total_profit) as total_amount, COUNT(*) as total_count
                           FROM transactions
                           WHERE transaction_type = '$current_type'" . $date_condition;
            $stats_result = $conn->query($stats_query);
            if ($stats_result && $stats_row = $stats_result->fetch_assoc()) {
                $summary[$current_type . '_amount'] = $stats_row['total_amount'] ?? 0.00;
                $summary[$current_type . '_count'] = $stats_row['total_count'] ?? 0;
                $stats_result->free(); // Free result set
            } else {
                error_log("Error calculating $current_type stats: " . $conn->error);
                // Ensure defaults are set even if query fails
                $summary[$current_type . '_amount'] = 0.00;
                $summary[$current_type . '_count'] = 0;
            }
        }

        // --- Query for donations and calculate stats ---
        if (in_array('donation', $types_to_process)) { // Only process if 'donation' is selected or 'all'

            // Fetch individual donations if needed (for the main table display)
            if ($type_filter === 'all' || $type_filter === 'donation') {
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
                    $result->free(); // Free result set
                } else {
                    error_log("Error fetching donations: " . $conn->error);
                }
            }


            // Calculate donation sum and count, applying date condition
            $don_stats_query = "SELECT SUM(donation_amount) as total_amount, COUNT(*) as total_count
                               FROM donations
                               WHERE transaction_type = 'donation'" . $date_condition;
            $don_stats_result = $conn->query($don_stats_query);
             if ($don_stats_result && $don_stats_row = $don_stats_result->fetch_assoc()) {
                 $summary['donation_amount'] = $don_stats_row['total_amount'] ?? 0.00;
                 $summary['donation_count'] = $don_stats_row['total_count'] ?? 0;
                 $don_stats_result->free(); // Free result set
            } else {
                 error_log("Error calculating donation stats: " . $conn->error);
                 // Ensure defaults are set even if query fails
                 $summary['donation_amount'] = 0.00;
                 $summary['donation_count'] = 0;
            }
        }

        // Calculate overall totals based on the filtered types processed
        $total_count = 0;
        $total_amount = 0.00;

        // Sum up counts and amounts from the already calculated per-type summaries
        foreach ($types_to_process as $type) {
            if (isset($summary[$type.'_count'])) {
                $total_count += $summary[$type.'_count'];
            }
             // Sum the *unformatted* amounts stored in the summary array before they get formatted below
             if (isset($summary[$type.'_amount'])) {
                 $total_amount += (float)$summary[$type.'_amount'];
             }
        }


        // Format summary amount values and add overall totals
        $formatted_summary = [];
        foreach ($summary as $key => $value) {
            if (strpos($key, '_amount') !== false) {
                 // Format per-type amounts
                 $formatted_summary[$key] = number_format((float)$value, 2);
            } else {
                 $formatted_summary[$key] = (int)$value; // Ensure count is integer
            }
        }
        // Add formatted overall totals
        $formatted_summary['total_count'] = $total_count;
        $formatted_summary['total_amount'] = number_format($total_amount, 2);


        // Send the combined response
        header('Content-Type: application/json');
        // Sort transactions by date and time if needed before sending
         usort($transactions, function($a, $b) {
             $datetimeA = strtotime($a['date'] . ' ' . $a['time']);
             $datetimeB = strtotime($b['date'] . ' ' . $b['time']);
             return $datetimeA <=> $datetimeB; // Sort ascending
         });
        error_log('Sending summary: ' . print_r($formatted_summary, true)); // Log the final summary being sent
        echo json_encode(['transactions' => $transactions, 'summary' => $formatted_summary]); // Send formatted summary
        exit;

    } catch (Exception $e) {
        error_log('Error in AJAX handler: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'An internal server error occurred: ' . $e->getMessage()]);
        exit;
    } finally {
         if (isset($conn)) {
             $conn->close(); // Ensure connection is closed
         }
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
             <!-- Counts Box -->
             <div class="summary-box" id="summary-counts">
                 <h4>Transaction Counts</h4>
                 <div class="summary-details"> <!-- Wrap details for better structure -->
                     <div class="summary-item"><span>Registrations:</span> <span id="count_registration">0</span></div>
                     <div class="summary-item"><span>Tickets:</span> <span id="count_tickets">0</span></div>
                     <div class="summary-item"><span>Shop:</span> <span id="count_shop">0</span></div>
                     <div class="summary-item"><span>Donations:</span> <span id="count_donation">0</span></div>
                 </div>
                 <div class="summary-total"> <!-- Add total section -->
                     <span><strong>Total Count:</strong></span> <span id="overall_count"><strong>0</strong></span>
                 </div>
             </div>
             <!-- Amounts Box -->
             <div class="summary-box" id="summary-amounts">
                 <h4>Transaction Amounts</h4>
                 <div class="summary-details"> <!-- Wrap details -->
                     <div class="summary-item"><span>Registrations:</span> <span id="amount_registration">$0.00</span></div>
                     <div class="summary-item"><span>Tickets:</span> <span id="amount_tickets">$0.00</span></div>
                     <div class="summary-item"><span>Shop:</span> <span id="amount_shop">$0.00</span></div>
                     <div class="summary-item"><span>Donations:</span> <span id="amount_donation">$0.00</span></div>
                 </div>
                  <div class="summary-total"> <!-- Add total section -->
                     <span><strong>Total Amount:</strong></span> <span id="overall_amount"><strong>$0.00</strong></span>
                 </div>
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
            const tableBody = document.getElementById('transactionTableBody');
            // Get references to the new total elements
            const overallCountEl = document.getElementById('overall_count');
            const overallAmountEl = document.getElementById('overall_amount');


            // Reset summary fields and show loading
            // Counts
            document.getElementById('count_registration').textContent = '...';
            document.getElementById('count_tickets').textContent = '...';
            document.getElementById('count_shop').textContent = '...';
            document.getElementById('count_donation').textContent = '...';
            overallCountEl.innerHTML = '<strong>...</strong>'; // Reset overall count
            // Amounts
            document.getElementById('amount_registration').textContent = 'Loading...';
            document.getElementById('amount_tickets').textContent = 'Loading...';
            document.getElementById('amount_shop').textContent = 'Loading...';
            document.getElementById('amount_donation').textContent = 'Loading...';
            overallAmountEl.innerHTML = '<strong>Loading...</strong>'; // Reset overall amount


            // Add loading indicator to table
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';


            // Make the fetch request
            fetch('transaction_report.php', {
                 // ... (fetch options) ...
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
                // ... (response check) ...
                if (!response.ok) {
                     return response.text().then(text => {
                         throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                     });
                }
                return response.text();
            })
            .then(text => {
                try {
                    // Try to parse the JSON
                    const data = JSON.parse(text);
                    console.log('Received data:', data); // Log received data

                    tableBody.innerHTML = ''; // Clear loading indicator

                    // Check if we got an error message from PHP (within the JSON)
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // --- Update Summary Boxes ---
                    const summary = data.summary || {}; // Default to empty object if summary is missing
                    console.log('Updating summary with:', summary); // Log summary data

                    // Update Counts Box (individual + total)
                    document.getElementById('count_registration').textContent = summary.registration_count ?? 0;
                    document.getElementById('count_tickets').textContent = summary.tickets_count ?? 0;
                    document.getElementById('count_shop').textContent = summary.shop_count ?? 0;
                    document.getElementById('count_donation').textContent = summary.donation_count ?? 0;
                    overallCountEl.innerHTML = `<strong>${summary.total_count || 0}</strong>`; // Update overall count

                    // Update Amounts Box (individual + total)
                    document.getElementById('amount_registration').textContent = '$' + (summary.registration_amount || '0.00');
                    document.getElementById('amount_tickets').textContent = '$' + (summary.tickets_amount || '0.00');
                    document.getElementById('amount_shop').textContent = '$' + (summary.shop_amount || '0.00');
                    document.getElementById('amount_donation').textContent = '$' + (summary.donation_amount || '0.00');
                    overallAmountEl.innerHTML = `<strong>$${summary.total_amount || '0.00'}</strong>`; // Update overall amount


                    // --- Update Table ---
                    const transactions = data.transactions || []; // Default to empty array if transactions missing
                     if (!Array.isArray(transactions)) {
                         console.error("Received 'transactions' is not an array:", transactions);
                         throw new Error("Invalid transaction data received from server.");
                    }

                    if (transactions.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No transactions found for the selected criteria.</td></tr>';
                    } else {
                        // Display the data in the table
                        transactions.forEach(transaction => {
                             // ... (create and append transaction rows using textContent) ...
                             const row = document.createElement('tr');
                             const numberCell = document.createElement('td');
                             numberCell.textContent = transaction.transaction_number || '';
                             const dateCell = document.createElement('td');
                             dateCell.textContent = transaction.date || '';
                             const timeCell = document.createElement('td');
                             timeCell.textContent = transaction.time || '';
                             const amountCell = document.createElement('td');
                             amountCell.textContent = transaction.amount || '0.00';
                             const typeCell = document.createElement('td');
                             typeCell.textContent = transaction.type || '';

                             row.appendChild(numberCell);
                             row.appendChild(dateCell);
                             row.appendChild(timeCell);
                             row.appendChild(amountCell);
                             row.appendChild(typeCell);
                             tableBody.appendChild(row);
                        });
                    }

                } catch (e) {
                    console.error('JSON Parse or Processing Error:', e);
                    console.log('Raw server response:', text); // Log raw text for debugging
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error processing server response. Check console for details.</td></tr>';
                     // Reset summary fields on processing error
                    document.getElementById('count_registration').textContent = '0';
                    document.getElementById('count_tickets').textContent = '0';
                    document.getElementById('count_shop').textContent = '0';
                    document.getElementById('count_donation').textContent = '0';
                    overallCountEl.innerHTML = '<strong>0</strong>'; // Reset overall count
                    document.getElementById('amount_registration').textContent = '$0.00';
                    document.getElementById('amount_tickets').textContent = '$0.00';
                    document.getElementById('amount_shop').textContent = '$0.00';
                    document.getElementById('amount_donation').textContent = '$0.00';
                    overallAmountEl.innerHTML = '<strong>$0.00</strong>'; // Reset overall amount
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: red;">
                            Error loading transactions: ${error.message}. Please check connection or server logs.
                        </td>
                    </tr>`;
                 // Reset summary fields on fetch error
                 document.getElementById('count_registration').textContent = '0';
                 document.getElementById('count_tickets').textContent = '0';
                 document.getElementById('count_shop').textContent = '0';
                 document.getElementById('count_donation').textContent = '0';
                 overallCountEl.innerHTML = '<strong>0</strong>'; // Reset overall count
                 document.getElementById('amount_registration').textContent = '$0.00';
                 document.getElementById('amount_tickets').textContent = '$0.00';
                 document.getElementById('amount_shop').textContent = '$0.00';
                 document.getElementById('amount_donation').textContent = '$0.00';
                 overallAmountEl.innerHTML = '<strong>$0.00</strong>'; // Reset overall amount
            });
        }
    </script>
</div>

<?php
 // Close connection if it wasn't closed in the AJAX handler (e.g., non-AJAX request)
 if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
     $conn->close();
 }
?>