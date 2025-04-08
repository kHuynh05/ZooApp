<!-- navbar.php -->
<?php
include '../config/database.php';
include '../scripts/authorize.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoo Home Page</title>
    <link rel="stylesheet" href="../assets/css/global.css">
</head>
<nav class="navbar">
    <div class="navbar-logo">
        <a href="homepage.php">
            <img src="/assets/img/zoo-logo.jpg" alt="Houston Zoo Logo">
        </a>
    </div>
    <div class="navbar-links">
            <a href="animals-info.php">Animals</a>
            <a href="ticket.php">Tickets</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a> 
            <!-- Profile Dropdown options -->
            <div class="profile-dropdown"> 
            <button class="profile-icon">
                <i class="fas fa-user"></i>
            </button>
            <div class="dropdown-content">
                <?php
                if ($is_member) {
                    echo '<a href="memberPortal.php">Member Portal</a>';
                    echo '<a href="../scripts/logout.php">Logout</a>';
                } else {
                    echo '<a href="login.php">Login</a>';
                }
                ?>
            </div>
        </div>
    </div>
</nav>
<!-- Add Font Awesome for the user icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


<style>
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar-logo {
        margin-right: auto;

    }

    .navbar-logo img {
        max-height: 120px;
        width: auto;

    }

    .navbar-links {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .navbar-links a {
        color: #333333;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
        font-size: 24px;
    }

    .navbar-links a:hover {
        color: #2c5f2d;
    }

    .profile-dropdown {
        position: relative;
        display: inline-block;
        margin-left: 2rem;
    }

    .profile-icon {
        background: none;
        border: none;
        color: #333333;
        font-size: 24px;
        cursor: pointer;
        padding: 8px;
        transition: color 0.3s ease;
    }

    .profile-icon:hover {
        color: #2c5f2d;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        z-index: 1;
        border-radius: 8px;
        overflow: hidden;
    }

    .dropdown-content a {
        color: #333333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 16px;
    }

    .dropdown-content a:hover {
        background-color: #f5f5f5;
    }

    .profile-dropdown:hover .dropdown-content {
        display: block;
    }
</style>
