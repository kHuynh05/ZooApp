<?php
include '../config/database.php';
$permissions = [
    "admin" => [
        "create_employees",
        "remove_employee",
        "create_events",
        "view_events",
        "view_transaction_report",
        "view_reports",
        "logout"
    ],
    "manager" => [
        "view_employees",
        "view_reports",
        "update_animals",
        "assign_care",
        "logout"
    ],
    "shop" => [
        "process_transactions",
        "generate_sales_reports",
        "logout"
    ],
    "vet" => [
        "provide_medical_assistance",
        "maintain_medical_records",
        "logout"
    ],
    "care" => [
        "maintain_medical_records",
        "feed_animals",
        "maintain_enclosures",
        "logout"
    ]
];


$user_role = $_SESSION['role'];

if ($user_role == null) {
    header("Location: ../public/employeeLogin.php");
    $_SESSION['message'] = "You must log in first.";
    exit();
}

$allowed_actions = $permissions[$user_role] ?? [];
