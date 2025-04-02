<?php
include '../scripts/employeeRole.php';
?>

<head>
    <link rel="stylesheet" href="../assets/css/employeePortal.css">
</head>
<div class="container">
    <div class="tab-container">
        <h1 class="member-title">Employee Portal</h1>
        <?php
        $tabs = [
            "create_employees" => ["title" => "Create Employees", "id" => "create_employees"],
            "create_events" => ["title" => "Create Events", "id" => "create_events"],
            "view_events" => ["title" => "View Events", "id" => "view_events"],
            "view_transaction_report" => ["title" => "Transaction Report", "id" => "transaction_report"],
            "view_customers" => ["title" => "View Customers", "id" => "view_customers"],
            "view_reports" => ["title" => "View Reports", "id" => "view_reports"],
            "view_employees" => ["title" => "View Employees", "id" => "view_employees"],
            "handle_vet_requests" => ["title" => "Vet Requests", "id" => "vet_requests"],
            "process_transactions" => ["title" => "Process Transactions", "id" => "process_transactions"],
            "generate_sales_reports" => ["title" => "Sales Reports", "id" => "sales_reports"],
            "provide_medical_assistance" => ["title" => "Medical Assistance", "id" => "medical_assistance"],
            "maintain_medical_records" => ["title" => "Medical Records", "id" => "medical_records"],
            "feed_animals" => ["title" => "Feed Animals", "id" => "feed_animals"],
            "maintain_enclosures" => ["title" => "Maintain Enclosures", "id" => "maintain_enclosures"],
            "logout" => ["title" => "Logout", "id" => "logout"]  
        ];

        foreach ($tabs as $permission => $tab) {
            if (in_array($permission, $allowed_actions)) {
                echo '<div class="tab" onclick="showTab(\'' . $tab['id'] . '\')">' . $tab['title'] . '</div>';
            }
        }
        ?>
    </div>

    <div class="tab-content">
        <?php
        foreach ($tabs as $permission => $tab) {
            if (in_array($permission, $allowed_actions)) {
                echo '<div id="' . $tab['id'] . '" class="tab-pane hidden">';
                include $tab['id'] . ".php"; 
                echo '</div>';
            }
        }
        ?>
    </div>
</div>
<script>
    window.onload = function() {
        var firstTab = document.querySelector('.tab');
        if (firstTab) {
            var firstTabId = firstTab.getAttribute('onclick').match(/'([^']+)'/)[1];
            showTab(firstTabId);
        }
    };

    function showTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.add('hidden'));

        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

        document.getElementById(tabId).classList.remove('hidden');

        var clickedTab = document.querySelector(`.tab[onclick="showTab('${tabId}')"]`);
        if (clickedTab) {
            clickedTab.classList.add('active');
        }
    }
</script>