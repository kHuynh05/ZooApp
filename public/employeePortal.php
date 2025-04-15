<?php
include '../scripts/employeeRole.php';
?>

<head>
    <link rel="stylesheet" href="../assets/css/employeePortal.css">
    <script>
        // This should run as soon as the script is loaded
        console.log("Script loaded - before window.onload");

        // Add an event listener directly - an alternative to window.onload
        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOMContentLoaded event fired");

            try {
                // Log allowed actions
                console.log("Allowed actions:", <?php echo json_encode($allowed_actions); ?>);

                // Get all tabs
                var allTabs = document.querySelectorAll('.tab');
                console.log("Total tabs found:", allTabs.length);

                // Find visible tabs
                var visibleTabs = Array.from(allTabs).filter(tab => {
                    try {
                        var onclick = tab.getAttribute('onclick');
                        var match = onclick.match(/'([^']+)'/);
                        if (!match) {
                            console.log("No match found for:", onclick);
                            return false;
                        }
                        var tabId = match[1];
                        var isAllowed = <?php echo json_encode($allowed_actions); ?>.includes(tabId);
                        console.log("Tab:", tabId, "Allowed:", isAllowed);
                        return isAllowed;
                    } catch (error) {
                        console.error("Error processing tab:", error);
                        return false;
                    }
                });

                console.log("Visible tabs count:", visibleTabs.length);

                // Show first tab if any exist
                if (visibleTabs.length > 0) {
                    var firstVisibleTab = visibleTabs[0];
                    console.log("First visible tab:", firstVisibleTab.textContent);
                    var onclick = firstVisibleTab.getAttribute('onclick');
                    var match = onclick.match(/'([^']+)'/);
                    var firstTabId = match[1];
                    console.log("Will show tab:", firstTabId);
                    showTab(firstTabId);
                } else {
                    console.warn("No tabs available for this user.");
                }
            } catch (error) {
                console.error("Error in initialization:", error);
            }
        });

        // The original window.onload function
        window.onload = function() {
            console.log("Window.onload fired");
            var visibleTabs = Array.from(document.querySelectorAll('.tab')).filter(tab => {
                var tabId = tab.getAttribute('onclick').match(/'([^']+)'/)[1];
                return <?php echo json_encode($allowed_actions); ?>.includes(tabId);
            });

            // Show the first visible tab if any exist
            if (visibleTabs.length > 0) {
                var firstVisibleTab = visibleTabs[0];
                var firstTabId = firstVisibleTab.getAttribute('onclick').match(/'([^']+)'/)[1];
                showTab(firstTabId); // Show the first visible tab
            } else {
                // Optionally handle the case where no tabs are visible
                console.warn("No tabs available for this user.");
            }
        };

        function showTab(tabId) {
            console.log("showTab called with:", tabId);
            try {
                document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.add('hidden'));
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

                var tabElement = document.getElementById(tabId);
                if (tabElement) {
                    tabElement.classList.remove('hidden');
                    console.log("Tab unhidden:", tabId);
                } else {
                    console.error("Tab element not found:", tabId);
                }

                var clickedTab = document.querySelector(`.tab[onclick="showTab('${tabId}')"]`);
                if (clickedTab) {
                    clickedTab.classList.add('active');
                    console.log("Tab activated:", tabId);
                } else {
                    console.error("Tab button not found for:", tabId);
                }
            } catch (error) {
                console.error("Error in showTab:", error);
            }
        }
    </script>
</head>
<div class="container">
    <div class="tab-container">
        <h1 class="member-title">Employee Portal</h1>
        <?php
        $tabs = [
            "create_employees" => ["title" => "Create Employees", "id" => "create_employees"],
            "edit_employee" => ["title" => "Edit Employee", "id" => "edit_employee"],
            "remove_employee" => ["title" => "Remove Employee", "id" => "remove_employee"],
            "create_events" => ["title" => "Create Events", "id" => "create_events"],
            "view_events" => ["title" => "View Events", "id" => "view_events"],
            "view_transaction_report" => ["title" => "Transaction Report", "id" => "transaction_report"],
            "view_reports" => ["title" => "View Reports", "id" => "view_reports"],
            "view_employees" => ["title" => "View Employees", "id" => "view_employees"],
            "view_reports" => ["title" => "View Reports", "id" => "view_reports"],
            "update_animals" => ["title" => "Update Animals", "id" => "update_animals"],
            "assign_care" => ["title" => "Assign Care", "id" => "assign_care"],
            "process_transactions" => ["title" => "Process Transactions", "id" => "process_transactions"],
            "generate_sales_reports" => ["title" => "Sales Reports", "id" => "sales_reports"],
            "medical_assistance" => ["title" => "Medical Assistance", "id" => "medical_assistance"],
            "medical_records" => ["title" => "Medical Records", "id" => "medical_records"],
            "feed_animals" => ["title" => "Feed Animals", "id" => "feed_animals"],
            "maintain_enclosures" => ["title" => "Maintain Enclosures", "id" => "maintain_enclosures"],
            "add_animals" => ["title" => "Add Animals", "id" => "add_animals"],
            "delete_animals" => ["title" => "Delete Animals", "id" => "delete_animals"],
            "assign_caretaker" => ["title" => "Assign Caretaker", "id" => "assign_caretaker"],
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