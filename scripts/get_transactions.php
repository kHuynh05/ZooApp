<?php
include '../config/database.php';

// Hardcoded item prices
$item_prices = [
    'Zoo Toy' => 15.99,
    'Zoo Book' => 25.50,
    'Zoo Jacket' => 89.99,
    'Zoo Shirt' => 24.50,
    'Zoo Hat' => 19.99
];

// Initialize the base query
$query = "SELECT 
    t.transaction_number, 
    t.transaction_date, 
    i.item_name, 
    i.quantity
    FROM transactions t
    JOIN shop_items i ON t.transaction_number = i.transaction_id
    WHERE t.transaction_type = 'shop'";

$params = [];
$types = "";

// Handle transaction ID range filter
if (!empty($_POST['startId'])) {
    $query .= " AND t.transaction_number >= ?";
    $params[] = $_POST['startId'];
    $types .= "s";
}

if (!empty($_POST['endId'])) {
    $query .= " AND t.transaction_number <= ?";
    $params[] = $_POST['endId'];
    $types .= "s";
}

// Handle date range filter
if (!empty($_POST['startDate'])) {
    $query .= " AND DATE(t.transaction_date) >= ?";
    $params[] = $_POST['startDate'];
    $types .= "s";
}

if (!empty($_POST['endDate'])) {
    $query .= " AND DATE(t.transaction_date) <= ?";
    $params[] = $_POST['endDate'];
    $types .= "s";
}

// Handle item filter
if (!empty($_POST['items']) && is_array($_POST['items'])) {
    $placeholders = str_repeat('?,', count($_POST['items']) - 1) . '?';
    $query .= " AND i.item_name IN ($placeholders)";
    $params = array_merge($params, $_POST['items']);
    $types .= str_repeat('s', count($_POST['items']));
}

$query .= " ORDER BY t.transaction_date DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Process results
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $item_name = $row['item_name'];
    $quantity = $row['quantity'];
    $price = $item_prices[$item_name] ?? 0;
    $total = $quantity * $price;

    $transactions[] = [
        'transaction_number' => $row['transaction_number'],
        'transaction_date' => $row['transaction_date'],
        'item_name' => $item_name,
        'quantity' => $quantity,
        'price' => $price,
        'total' => $total
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['transactions' => $transactions]); 