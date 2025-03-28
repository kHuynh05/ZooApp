<?php
// Database connection
include '../config/database.php';

// Hardcoded item prices
$item_prices = [
    'Zoo Toy' => 15.99,
    'Zoo Book' => 25.50,
    'Zoo Jacket' => 89.99,
    'Zoo Shirt' => 24.50,
    'Zoo Hat' => 19.99
];

// Build the main query
$query = "SELECT 
    t.transaction_number, 
    t.transaction_date, 
    i.item_name, 
    i.quantity
    FROM transactions t
    JOIN shop_items i ON t.transaction_number = i.transaction_id
    WHERE t.transaction_type = 'shop'
    ORDER BY t.transaction_date DESC";

$result = $conn->query($query);

// Fetch unique items for the filter dropdown
$item_query = "SELECT DISTINCT item_name FROM shop_items ORDER BY item_name";
$item_result = $conn->query($item_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 300px;
            background-color: #f4f4f4;
            padding: 20px 20px 0px 20px;
            overflow-y: auto;
            max-height: calc(100vh - 100px);
            border-right: 1px solid #ddd;
        }

        .main-content {
            flex: 1;
            padding: 0px 20px;
            overflow-x: auto;
        }

        h1 {
            margin: 20px 20px 10px 0px;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .filter-row {
            margin-bottom: 15px;
        }
        .filter-row div{
            margin-bottom:15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select[multiple] {
            height: 300px;
        }

        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin:0px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .summary-card {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }

        #transaction-id-search {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        #transaction-id-search input {
            flex: 1;
        }

        .transaction-id-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .transaction-id-range input {
            flex: 1;
        }
    </style>
</head>
<body>
    <h1>Shop Sales Report</h1>
    
    <div class="container">
        <div class="sidebar">
            <!-- Transaction ID Search -->
            <div id="transaction-id-search">
                <div class="transaction-id-range">
                    <input type="text" id="transaction-id-start" placeholder="Start Transaction ID">
                    <span>to</span>
                    <input type="text" id="transaction-id-end" placeholder="End Transaction ID">
                </div>
                <button class="btn" onclick="searchByTransactionId()">Search</button>
            </div>
            
            <!-- Filter Form -->
            <div class="filter-container">
                <form id="filter-form">
                    <div class="filter-row">
                        <div>
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date">
                        </div>
                        <div>
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date">
                        </div>
                        <div>
                            <label for="items">Select Items</label>
                            <select id="items" multiple>
                                <?php while($item_row = $item_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($item_row['item_name']) ?>">
                                        <?= htmlspecialchars($item_row['item_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button type="button" class="btn" onclick="applyFilters()">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="main-content">
            <!-- Results Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Item Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="results-body">
                    <?php 
                    $transactions = [];
                    $total_revenue = 0;
                    $total_items_sold = 0;
                    $unique_transactions = [];
                    
                    while($row = $result->fetch_assoc()): 
                        $item_name = $row['item_name'];
                        $quantity = $row['quantity'];
                        $price = $item_prices[$item_name] ?? 0;
                        $total_line = $quantity * $price;
                        
                        // Store full transaction data for client-side filtering
                        $transactions[] = [
                            'transaction_number' => $row['transaction_number'],
                            'transaction_date' => $row['transaction_date'],
                            'item_name' => $item_name,
                            'quantity' => $quantity,
                            'price' => $price,
                            'total' => $total_line
                        ];
                    endwhile; 
                    ?>
                </tbody>
            </table>

            <!-- Summary Section -->
            <div class="summary-card" id="summary-section">
                <h5>Report Summary</h5>
                <p>Total Transactions: <span id="total-transactions">0</span></p>
                <p>Total Revenue: $<span id="total-revenue">0.00</span></p>
                <p>Total Items Sold: <span id="total-items-sold">0</span></p>
            </div>
        </div>
    </div>

    <script>
        // Full transaction data from PHP
        const allTransactions = <?= json_encode($transactions) ?>;
        const itemPrices = <?= json_encode($item_prices) ?>;

        function searchByTransactionId() {
            const startId = document.getElementById('transaction-id-start').value.trim().toLowerCase();
            const endId = document.getElementById('transaction-id-end').value.trim().toLowerCase();

            // Reset date and item filters
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('items').selectedIndex = -1;

            // Filter transactions by transaction ID range
            const filteredTransactions = allTransactions.filter(transaction => {
                const transactionNumber = transaction.transaction_number.toLowerCase();
                
                // If no start ID, match all up to end ID
                if (!startId && endId) {
                    return transactionNumber <= endId;
                }
                
                // If no end ID, match all from start ID
                if (startId && !endId) {
                    return transactionNumber >= startId;
                }
                
                // If both start and end IDs are provided
                if (startId && endId) {
                    return transactionNumber >= startId && transactionNumber <= endId;
                }
                
                // If no IDs are provided, return all transactions
                return true;
            });

            renderTransactions(filteredTransactions);
        }

        function applyFilters() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const selectedItems = Array.from(document.getElementById('items').selectedOptions)
                                       .map(option => option.value);

            // Reset transaction ID search
            document.getElementById('transaction-id-start').value = '';
            document.getElementById('transaction-id-end').value = '';

            // Validate dates
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('Start date must be before or equal to end date');
                return;
            }

            // Filter transactions
            const filteredTransactions = allTransactions.filter(transaction => {
                // Date filter
                const transactionDate = new Date(transaction.transaction_date);
                const start = startDate ? new Date(startDate) : null;
                const end = endDate ? new Date(endDate) : null;

                const dateCheck = (!start || transactionDate >= start) && 
                                  (!end || transactionDate <= end);

                // Item filter
                const itemCheck = selectedItems.length === 0 || 
                                  selectedItems.includes(transaction.item_name);

                return dateCheck && itemCheck;
            });

            renderTransactions(filteredTransactions);
        }

        function renderTransactions(filteredTransactions) {
            const resultsBody = document.getElementById('results-body');
            resultsBody.innerHTML = '';

            let totalRevenue = 0;
            let totalItemsSold = 0;
            const uniqueTransactions = new Set();

            filteredTransactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${transaction.transaction_number}</td>
                    <td>${transaction.transaction_date}</td>
                    <td>${transaction.item_name}</td>
                    <td>${transaction.quantity}</td>
                    <td>$${transaction.price.toFixed(2)}</td>
                    <td>$${transaction.total.toFixed(2)}</td>
                `;
                resultsBody.appendChild(row);

                // Update summary calculations
                totalRevenue += transaction.total;
                totalItemsSold += transaction.quantity;
                uniqueTransactions.add(transaction.transaction_number);
            });

            // Update summary section
            document.getElementById('total-transactions').textContent = uniqueTransactions.size;
            document.getElementById('total-revenue').textContent = totalRevenue.toFixed(2);
            document.getElementById('total-items-sold').textContent = totalItemsSold;
        }

        // Initial load of all transactions
        function initialLoad() {
            document.getElementById('start_date').value = '<?= date('Y-m-01') ?>';
            document.getElementById('end_date').value = '<?= date('Y-m-d') ?>';
            renderTransactions(allTransactions);
        }

        // Add multiple select functionality hint
        const itemsSelect = document.getElementById('items');
        const hint = document.createElement('small');
        hint.textContent = 'Tip: Hold Ctrl (Cmd on Mac) to select multiple items';
        hint.style.display = 'block';
        hint.style.color = '#666';
        hint.style.marginTop = '5px';
        itemsSelect.parentNode.insertBefore(hint, itemsSelect.nextSibling);

        // Load initial data when page loads
        window.onload = initialLoad;
    </script>
</body>
</html>