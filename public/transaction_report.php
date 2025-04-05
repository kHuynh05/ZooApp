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
        $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $end_date = !empty($data['end_date']) ? $data['end_date'] : null;
        $type = isset($data['type']) ? $data['type'] : 'all';

        $transactions = [];
        
        // Query for regular transactions
        if ($type === 'all' || $type !== 'donation') {
            $trans_query = "SELECT 
                transaction_number,
                DATE(transaction_date) as date,
                transaction_time as time,
                total_profit as amount,
                transaction_type as type
                FROM transactions WHERE 1=1";

            if ($start_date) {
                $trans_query .= " AND transaction_date >= '$start_date'";
            }
            if ($end_date) {
                $trans_query .= " AND transaction_date <= '$end_date'";
            }
            if ($type !== 'all') {
                $trans_query .= " AND transaction_type = '$type'";
            }

            $result = $conn->query($trans_query);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $transactions[] = [
                        'transaction_number' => $row['transaction_number'],
                        'date' => $row['date'],
                        'time' => $row['time'],
                        'amount' => number_format($row['amount'], 2),
                        'type' => $row['type']
                    ];
                }
            }
        }

        // Query for donations
        if ($type === 'all' || $type === 'donation') {
            $don_query = "SELECT 
                transaction_number,
                DATE(transaction_date) as date,
                TIME(transaction_date) as time,
                donation_amount as amount,
                transaction_type as type
                FROM donations 
                WHERE transaction_type = 'donation'";

            if ($start_date) {
                $don_query .= " AND DATE(transaction_date) >= '$start_date'";
            }
            if ($end_date) {
                $don_query .= " AND DATE(transaction_date) <= '$end_date'";
            }

            $result = $conn->query($don_query);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $transactions[] = [
                        'transaction_number' => 'D' . $row['transaction_number'],
                        'date' => $row['date'],
                        'time' => $row['time'],
                        'amount' => number_format($row['amount'], 2),
                        'type' => 'donation'
                    ];
                }
            }
        }

        // Send the response
        header('Content-Type: application/json');
        echo json_encode($transactions);
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

            // Add loading indicator
            const tableBody = document.getElementById('transactionTableBody');
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';

            // Create the request data
            const requestData = {
                start_date: startDate,
                end_date: endDate,
                type: type
            };

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
                    
                    // Clear the table
                    tableBody.innerHTML = '';

                    // Check if we got an error message
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Check if we got any data
                    if (!Array.isArray(data) || data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No transactions found</td></tr>';
                        return;
                    }

                    // Display the data
                    data.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${transaction.transaction_number || ''}</td>
                            <td>${transaction.date || ''}</td>
                            <td>${transaction.time || ''}</td>
                            <td>${typeof transaction.amount === 'number' ? transaction.amount.toFixed(2) : transaction.amount || '0.00'}</td>
                            <td>${transaction.type || ''}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.log('Raw response:', text);
                    throw new Error('Failed to parse server response');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: red;">
                            Error loading transactions. Please try again.
                        </td>
                    </tr>`;
            });
        }
    </script>
</div>