<?php
include '../scripts/employeeRole.php';
if($user_role == "care"){
    $stmt =  $conn -> prepare("SELECT enclosure_id FROM employees WHERE role = ? AND emp_id = ?");
    $stmt = $conn -> bind_param("si", $user_role, 'SESSION'['emp_id']);
}
?>