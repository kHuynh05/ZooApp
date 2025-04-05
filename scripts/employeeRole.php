<?php
include '../config/database.php';
$permissions = [
    "admin" => [
        "create_employees", 
        "create_events", 
        "view_events",
        "view_transaction_report", 
        "view_customers", 
        "view_contact_concerns",
        "logout"
    ],
    "manager" => [
        "view_employees", 
        "view_contact_concerns", 
        "add_animals",
        "delete_animals",
        "assign_caretaker",
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

if($user_role == null){
    header("Location: ../public/employeeLogin.php");
    $_SESSION['message'] = "You must log in first.";
    exit();
}

$allowed_actions = $permissions[$user_role] ?? [];
?>