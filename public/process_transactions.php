<?php
include '../config/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction at the database level
        $conn->begin_transaction();

        // Prepare transaction insert
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        $cust_id = 0;
        $type = "shop";
        $total_amount = $_POST['total_amount'];

        $stmt_transaction = $conn->prepare("INSERT INTO transactions (transaction_date, transaction_time, cust_id, total_profit, transaction_type) 
    VALUES (?, ?, ?, ?, ?)");
        $stmt_transaction->bind_param("ssids", $current_date, $current_time, $cust_id, $total_amount, $type);

        // Execute transaction insert
        if (!$stmt_transaction->execute()) {
            throw new Exception("Failed to insert transaction: " . $stmt_transaction->error);
        }

        // Get the last inserted transaction ID
        $transaction_id = $conn->insert_id;

        // Prepare items insert
        $stmt_items = $conn->prepare("INSERT INTO shop_items (transaction_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_items->bind_param("isid", $transaction_id, $item_name, $quantity, $price);

        // Process each selected item
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $prices = [
                'Zoo Toy' => 15.99,
                'Zoo Book' => 25.50,
                'Zoo Jacket' => 89.99,
                'Zoo Shirt' => 24.50,
                'Zoo Hat' => 19.99
            ];

            foreach ($_POST['items'] as $index => $item_name) {
                $quantity = $_POST['quantities'][$index];
                $price = $prices[$item_name];

                // Execute each item insert
                if (!$stmt_items->execute()) {
                    throw new Exception("Failed to insert item: " . $stmt_items->error);
                }
            }
        }

        // Commit the transaction
        $conn->commit();
        
        // Redirect to employee portal
        header("Location: ../public/employeePortal.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Log the error
        var_dump("Transaction failed: " . $e->getMessage());

        // Send error response
        echo json_encode(['status' => 'error', 'message' => 'Transaction failed. Please try again.']);
    } finally {
        // Close statements and connection
        if (isset($stmt_transaction)) $stmt_transaction->close();
        if (isset($stmt_items)) $stmt_items->close();
        $conn->close();
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>Zoo Store Transaction</title>
    <style>

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
        }

        #totalAmount {
            font-size: 1.2em;
            color: #007bff;
        }

        .submit-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="transaction-container">
        <h1>Zoo Store Transaction</h1>
        <form id="transactionForm" action="process_transactions.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="Zoo Toy" data-price="15.99"></td>
                        <td>Zoo Toy</td>
                        <td>$15.99</td>
                        <td><input type="number" name="quantities[]" min="1" max="10" value="1" disabled></td>
                        <td class="subtotal">$0.00</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="Zoo Book" data-price="25.50"></td>
                        <td>Zoo Book</td>
                        <td>$25.50</td>
                        <td><input type="number" name="quantities[]" min="1" max="10" value="1" disabled></td>
                        <td class="subtotal">$0.00</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="Zoo Jacket" data-price="89.99"></td>
                        <td>Zoo Jacket</td>
                        <td>$89.99</td>
                        <td><input type="number" name="quantities[]" min="1" max="10" value="1" disabled></td>
                        <td class="subtotal">$0.00</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="Zoo Shirt" data-price="24.50"></td>
                        <td>Zoo Shirt</td>
                        <td>$24.50</td>
                        <td><input type="number" name="quantities[]" min="1" max="10" value="1" disabled></td>
                        <td class="subtotal">$0.00</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="items[]" value="Zoo Hat" data-price="19.99"></td>
                        <td>Zoo Hat</td>
                        <td>$19.99</td>
                        <td><input type="number" name="quantities[]" min="1" max="10" value="1" disabled></td>
                        <td class="subtotal">$0.00</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4">Total Amount</td>
                        <td id="totalAmount">$0.00</td>
                    </tr>
                </tbody>
            </table>

            <input type="hidden" name="total_amount" id="hiddenTotalAmount" value="0.00">
            <button type="submit" class="submit-btn">Process Transaction</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const quantityInputs = document.querySelectorAll('input[type="number"]');
            const subtotalCells = document.querySelectorAll('.subtotal');
            const totalAmountCell = document.getElementById('totalAmount');
            const hiddenTotalAmount = document.getElementById('hiddenTotalAmount');
            const transactionForm = document.getElementById('transactionForm');

            function updatePricing() {
                let total = 0;

                checkboxes.forEach((checkbox, index) => {
                    const price = parseFloat(checkbox.dataset.price);
                    const quantityInput = quantityInputs[index];
                    const subtotalCell = subtotalCells[index];

                    if (checkbox.checked) {
                        quantityInput.disabled = false;
                        const quantity = parseInt(quantityInput.value);
                        const subtotal = price * quantity;
                        subtotalCell.textContent = `$${subtotal.toFixed(2)}`;
                        total += subtotal;
                    } else {
                        quantityInput.disabled = true;
                        subtotalCell.textContent = '$0.00';
                    }
                });

                totalAmountCell.textContent = `$${total.toFixed(2)}`;
                hiddenTotalAmount.value = total.toFixed(2);
            }

            // Ensure initial update
            updatePricing();

            checkboxes.forEach((checkbox, index) => {
                checkbox.addEventListener('change', () => {
                    quantityInputs[index].value = 1;
                    updatePricing();
                });

                quantityInputs[index].addEventListener('change', updatePricing);
            });

            // Add form validation
            transactionForm.addEventListener('submit', (e) => {
                const totalAmount = parseFloat(hiddenTotalAmount.value);
                if (totalAmount <= 0) {
                    e.preventDefault();
                    alert('Please select at least one item.');
                }
            });
        });
    </script>
</body>