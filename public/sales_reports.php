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

// Fetch unique items for the filter dropdown
$item_query = "SELECT DISTINCT item_name FROM shop_items ORDER BY item_name";
$item_result = $conn->query($item_query);

// Fetch summary data for all items
$summarySql = "SELECT 
    item_name,
    SUM(quantity) as total_quantity,
    SUM(quantity * price) as total_revenue
    FROM shop_items 
    GROUP BY item_name
    ORDER BY total_quantity DESC";

$summaryResult = $conn->query($summarySql);
$summaryData = [];
$totalRevenue = 0;
$totalItems = 0;

while ($row = $summaryResult->fetch_assoc()) {
    $summaryData[] = $row;
    $totalRevenue += $row['total_revenue'];
    $totalItems += $row['total_quantity'];
}

// Get most and least popular items
$mostPopular = $summaryData[0] ?? null;
$leastPopular = end($summaryData) ?? null;

// Fetch transaction summary data
$transactionSql = "SELECT 
    COUNT(DISTINCT t.transaction_number) as total_transactions,
    AVG(si.quantity) as avg_items_per_transaction,
    MAX(t.transaction_date) as last_transaction_date,
    (SELECT COUNT(DISTINCT t2.transaction_number) 
     FROM transactions t2
     JOIN shop_items si2 ON t2.transaction_number = si2.transaction_id
     WHERE t2.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as weekly_transactions
    FROM transactions t
    JOIN shop_items si ON t.transaction_number = si.transaction_id";

$transactionResult = $conn->query($transactionSql);
$transactionData = $transactionResult->fetch_assoc();
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

        .filter-row div {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select {
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
            margin: 0px;
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

        .table th,
        .table td {
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

        .summary-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 20px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .summary-section {
            flex: 1;
            padding: 0 20px;
            border-right: 1px solid #ddd;
        }

        .summary-section:last-child {
            border-right: none;
        }

        .summary-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.1em;
            font-weight: bold;
        }

        .summary-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-section li {
            margin-bottom: 10px;
            color: #666;
            font-size: 0.95em;
        }

        #item-breakdown {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 10px;
        }

        #item-breakdown li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        #item-breakdown li:last-child {
            border-bottom: none;
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
                                <?php while ($item_row = $item_result->fetch_assoc()): ?>
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
            <div class="summary-container">
                <div class="summary-section">
                    <h3>Overall Sales Summary</h3>
                    <ul>
                        <li>Total Revenue: $<span id="total-revenue"><?php echo number_format($totalRevenue, 2); ?></span></li>
                        <li>Total Items Sold: <span id="total-items"><?php echo $totalItems; ?></span></li>
                        <li>Most Popular Item: <span id="most-popular"><?php echo $mostPopular ? htmlspecialchars($mostPopular['item_name']) . " (Sold: " . $mostPopular['total_quantity'] . ")" : "N/A"; ?></span></li>
                        <li>Least Popular Item: <span id="least-popular"><?php echo $leastPopular ? htmlspecialchars($leastPopular['item_name']) . " (Sold: " . $leastPopular['total_quantity'] . ")" : "N/A"; ?></span></li>
                    </ul>
                </div>

                <div class="summary-section">
                    <h3>Transaction Summary</h3>
                    <ul>
                        <li>Total Transactions: <span id="total-transactions"><?php echo $transactionData['total_transactions']; ?></span></li>
                        <li>Average Items per Transaction: <span id="avg-items"><?php echo number_format($transactionData['avg_items_per_transaction'], 1); ?></span></li>
                        <li>Transactions This Week: <span id="weekly-transactions"><?php echo $transactionData['weekly_transactions']; ?></span></li>
                        <li>Last Transaction Date: <span id="last-transaction"><?php echo $transactionData['last_transaction_date']; ?></span></li>
                    </ul>
                </div>

                <div class="summary-section">
                    <h3>Item Breakdown</h3>
                    <ul id="item-breakdown">
                        <!-- Will be populated by JavaScript -->
                    </ul>
                </div>
            </div>
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
                    <!-- Results will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function searchByTransactionId() {
            const startId = document.getElementById('transaction-id-start').value.trim().toLowerCase();
            const endId = document.getElementById('transaction-id-end').value.trim().toLowerCase();

            // Reset date and item filters
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('items').selectedIndex = -1;

            // Fetch data with transaction ID filters
            fetchFilteredTransactions({
                startId: startId,
                endId: endId
            });
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

            // Fetch data with date and item filters
            fetchFilteredTransactions({
                startDate: startDate,
                endDate: endDate,
                items: selectedItems
            });
        }

        function fetchFilteredTransactions(filters) {
            // Show loading indicator
            const resultsBody = document.getElementById('results-body');
            resultsBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading...</td></tr>';

            // Create form data for the POST request
            const formData = new FormData();

            // Add all filters to the form data
            Object.keys(filters).forEach(key => {
                if (Array.isArray(filters[key])) {
                    // Handle arrays like selected items
                    filters[key].forEach(value => {
                        formData.append(`${key}[]`, value);
                    });
                } else if (filters[key]) {
                    // Only add non-empty values
                    formData.append(key, filters[key]);
                }
            });

            // Fetch data from PHP script
            fetch('../scripts/get_transactions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    renderTransactions(data.transactions);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    resultsBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading data. Please try again.</td></tr>';
                });
        }

        function calculateSummaries(transactions) {
            // Calculate overall totals
            let totalRevenue = 0;
            let totalItems = 0;
            const itemCounts = {};
            const itemRevenue = {};
            const uniqueTransactions = new Set();
            const weeklyTransactions = new Set();
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

            transactions.forEach(transaction => {
                totalRevenue += transaction.total;
                totalItems += transaction.quantity;
                uniqueTransactions.add(transaction.transaction_number);
                
                // Track weekly transactions
                const transactionDate = new Date(transaction.transaction_date);
                if (transactionDate >= oneWeekAgo) {
                    weeklyTransactions.add(transaction.transaction_number);
                }
                
                // Track item counts and revenue
                if (!itemCounts[transaction.item_name]) {
                    itemCounts[transaction.item_name] = 0;
                    itemRevenue[transaction.item_name] = 0;
                }
                itemCounts[transaction.item_name] += transaction.quantity;
                itemRevenue[transaction.item_name] += transaction.total;
            });

            // Find most and least popular items
            let mostPopular = { name: '', count: 0 };
            let leastPopular = { name: '', count: Infinity };
            
            Object.entries(itemCounts).forEach(([name, count]) => {
                if (count > mostPopular.count) {
                    mostPopular = { name, count };
                }
                if (count < leastPopular.count) {
                    leastPopular = { name, count };
                }
            });

            // Update the item breakdown list
            const breakdownList = document.getElementById('item-breakdown');
            breakdownList.innerHTML = '';
            
            Object.entries(itemCounts).forEach(([name, count]) => {
                const li = document.createElement('li');
                li.innerHTML = `${name}: ${count} sold ($${itemRevenue[name].toFixed(2)})`;
                breakdownList.appendChild(li);
            });

            return {
                totalRevenue,
                totalItems,
                mostPopular,
                leastPopular,
                itemCounts,
                itemRevenue,
                totalTransactions: uniqueTransactions.size,
                weeklyTransactions: weeklyTransactions.size,
                avgItemsPerTransaction: totalItems / uniqueTransactions.size
            };
        }

        function updateSummaryDisplay(summaries) {
            // Update overall summary
            document.getElementById('total-revenue').textContent = summaries.totalRevenue.toFixed(2);
            document.getElementById('total-items').textContent = summaries.totalItems;
            document.getElementById('most-popular').textContent = `${summaries.mostPopular.name} (Sold: ${summaries.mostPopular.count})`;
            document.getElementById('least-popular').textContent = `${summaries.leastPopular.name} (Sold: ${summaries.leastPopular.count})`;

            // Update transaction summary
            document.getElementById('total-transactions').textContent = summaries.totalTransactions;
            document.getElementById('avg-items').textContent = summaries.avgItemsPerTransaction.toFixed(1);
            document.getElementById('weekly-transactions').textContent = summaries.weeklyTransactions;
        }

        function renderTransactions(transactions) {
            const resultsBody = document.getElementById('results-body');
            resultsBody.innerHTML = '';

            if (transactions.length === 0) {
                resultsBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No transactions found matching the selected filters.</td></tr>';
                return;
            }

            let totalRevenue = 0;
            let totalItemsSold = 0;
            const uniqueTransactions = new Set();

            transactions.forEach(transaction => {
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
                totalItemsSold += parseInt(transaction.quantity);
                uniqueTransactions.add(transaction.transaction_number);
            });

            // Calculate and update summaries
            const summaries = calculateSummaries(transactions);
            updateSummaryDisplay(summaries);
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
        window.onload = () => fetchFilteredTransactions({});
    </script>
</body>

</html>