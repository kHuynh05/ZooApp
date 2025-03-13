<?php
// Required to access session variables
include '../config/database.php';

// Check if user is logged in
$is_member = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query to fetch member's information
    $query = "SELECT cust_id, first_name, last_name, cust_email, sex, date_of_birth, membership_type FROM members JOIN customers ON members.member_id = customers.cust_id WHERE member_id = ? AND membership_status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If a matching member is found, store all the member data in the session
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Store member details in session
        $query = "SELECT first_name, last_name, cust_email, date_of_birth, membership_type, reward_points, sex FROM customers JOIN members ON customers.cust_id = members.member_id WHERE customers.cust_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $email = $row['cust_email'];
            $dob = $row['date_of_birth'];
            $sex = $row['sex'];
            $membership_type = $row['membership_type'];
            $reward_points = $row['reward_points'];
        }

        $is_member = true;

        if ($is_member) {
            if($membership_type == "vip"){
                $member_discount = .4;
            }else if($membership_type == "premium"){
                $member_discount = .25;
            }else if($membership_type == "standard"){
                $member_discount = .1;
            }
        }else{
            $member_discount=0;
        }
    }

    $stmt->close();
}
