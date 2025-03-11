<?php
// Start the session
include '../config/database.php';
include '../scripts/authorize.php';
?>
<div class="container">
    <div class="tabs">
        <h1>Membership Portal</h1>
        <div class="tab active" onclick="showTab(1)">Dashboard</div>
        <div class="tab" onclick="showTab(2)">Membership</div>
        <div class="tab" onclick="showTab(3)">Tickets</div>
        <div class="tab" onclick="showTab(4)">Rewards</div>
    </div>
    <div></div>
</div>