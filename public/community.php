<?php
include '../config/database.php';
include '../scripts/authorize.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoo Community - Get Involved</title>
    <link rel="stylesheet" href="../assets/css/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include('../includes/navbar.php'); ?>
    
    <div class="hero-section">
        <h1>Join Our Zoo Community</h1>
        <p>Be part of something wild! Discover the many ways you can support wildlife conservation and education.</p>
    </div>

    <div class="container">
        <!-- Membership Section -->
        <section class="membership-section">
            <h2><i class="fas fa-id-card"></i> Become a Member</h2>
            <div class="membership-cards">
                <div class="membership-card">
                    <h3>Free Membership</h3>
                    <p class="price">$0/year</p>
                    <ul>
                        <li>Basic zoo access</li>
                        <li>Regular ticket prices</li>
                        <li>Newsletter subscription</li>
                    </ul>
                    <a href="register.php" class="cta-button">Join Now</a>
                </div>

                <div class="membership-card">
                    <h3>Standard Membership</h3>
                    <p class="price">$70/year</p>
                    <ul>
                        <li>15% discount on tickets</li>
                        <li>500 reward points on signup</li>
                        <li>Newsletter subscription</li>
                        <li>Member-only events</li>
                    </ul>
                    <a href="register.php" class="cta-button">Join Now</a>
                </div>

                <div class="membership-card featured">
                    <h3>Premium Membership</h3>
                    <p class="price">$120/year</p>
                    <ul>
                        <li>25% discount on tickets</li>
                        <li>500 reward points on signup</li>
                        <li>Newsletter subscription</li>
                        <li>Member-only events</li>
                        <li>Family benefits</li>
                    </ul>
                    <a href="register.php" class="cta-button">Join Now</a>
                </div>

                <div class="membership-card">
                    <h3>VIP Membership</h3>
                    <p class="price">$150/year</p>
                    <ul>
                        <li>40% discount on tickets</li>
                        <li>500 reward points on signup</li>
                        <li>Exclusive events access</li>
                        <li>Behind-the-scenes tours</li>
                        <li>Special gift shop discounts</li>
                    </ul>
                    <a href="register.php" class="cta-button">Join Now</a>
                </div>
            </div>
        </section>

        <!-- Donation Section -->
        <section class="donation-section">
            <h2><i class="fas fa-hand-holding-heart"></i> Support Our Mission</h2>
            <div class="donation-options">
                <div class="donation-card">
                    <h3>Animal Adoption Program</h3>
                    <p>Symbolically adopt an animal and support their care and conservation.</p>
                    <button class="cta-button" onclick="window.location.href='adopt.php'">Adopt Now</button>
                </div>
                
                <div class="donation-card">
                    <h3>Conservation Fund</h3>
                    <p>Support our wildlife conservation efforts and research programs.</p>
                    <button class="cta-button" onclick="window.location.href='donation.php'">Donate</button>
                </div>

                <div class="donation-card">
                    <h3>Education Initiative</h3>
                    <p>Help fund educational programs for schools and community outreach.</p>
                    <button class="cta-button" onclick="window.location.href='donation.php'">Support Education</button>
                </div>
            </div>
        </section>

        <!-- Events Section -->
        <section class="events-section">
            <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
            <div class="events-grid">
                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JUN</span>
                        <span class="day">15</span>
                    </div>
                    <div class="event-details">
                        <h3>Night at the Zoo</h3>
                        <p>Experience the zoo after dark with special nocturnal animal presentations.</p>
                        <button class="cta-button">Learn More</button>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-date">
                        <span class="month">JUL</span>
                        <span class="day">01</span>
                    </div>
                    <div class="event-details">
                        <h3>Conservation Day</h3>
                        <p>Join us for workshops and presentations about wildlife conservation.</p>
                        <button class="cta-button">Learn More</button>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-date">
                        <span class="month">AUG</span>
                        <span class="day">20</span>
                    </div>
                    <div class="event-details">
                        <h3>Members' Day</h3>
                        <p>Exclusive activities and behind-the-scenes tours for our members.</p>
                        <button class="cta-button">Learn More</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>