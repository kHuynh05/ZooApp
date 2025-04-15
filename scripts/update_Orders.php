<?php
include '../config/database.php';
include '../scripts/authorize.php';

$conn->begin_transaction();

try {
    // Get the JSON data
    $inputData = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($inputData["time"], $inputData["orderType"])) {
        throw new Exception("Invalid input data.");
    }

    $time = $inputData["time"];
    $orderType = $inputData["orderType"];

    // Convert $time to a valid date range
    $timeMap = [
        "1_month" => "INTERVAL 1 MONTH",
        "3_months" => "INTERVAL 3 MONTH",
        "6_months" => "INTERVAL 6 MONTH",
        "1_year" => "INTERVAL 1 YEAR"
    ];

    if (!isset($timeMap[$time])) {
        throw new Exception("Invalid time range.");
    }

    // Fetch relevant orders
    $stmt = $conn->prepare("
        SELECT transaction_number, transaction_date, transaction_time, transaction_type, total_profit 
        FROM transactions 
        WHERE 
        cust_id = ? AND
        transaction_type = ? 
        AND transaction_date >= NOW() - " . $timeMap[$time]
    );

    $stmt->bind_param("is", $user_id, $orderType);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    $stmt->close();
    $conn->commit();

    echo json_encode(["success" => true, "transactions" => $orders]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conn->close();
