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
        "edit_employee",
        "view_employees",
        "view_reports",
        "update_animals",
        "assign_care",
        "logout"
    ],
    "shop" => [
        "edit_employee",
        "process_transactions",
        "generate_sales_reports",
        "logout"
    ],
    "vet" => [
        "edit_employee",
        "medical_assistance",
        "medical_records",
        "logout"
    ],
    "care" => [
        "edit_employee",
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
