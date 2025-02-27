<head>
    <link rel="stylesheet" href="../assets/css/homepage.css">
</head>
<?php
// Include database connection
include '../config/database.php';  // Make sure the path is correct

// Fetch the upcoming events (you can adjust the query based on your database)
$sql = "SELECT event_id, event_name, event_date, description, picture FROM events ORDER BY event_date LIMIT 3";

$result = $conn->query($sql);

// Check if there are any events in the result
if ($result->num_rows > 0) {
    // Loop through the results and display each event
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
} else {
    // Handle the case if no events are found
    $events = [];
}

// Close the connection
$conn->close();
?>
<div class='container'>
    <?php include('../includes/navbar.php'); ?>
    <div class='homePage'>
        <div class='frontPage'>
            <div class='frontPage-text'>
                <span class='frontPage-intro'>WELCOME TO ZOOTOPIA</span>
                <span class='frontPage-motto'>WHERE WILDLIFE COMES TO LIFE</span>
                <a href="../public/ticket.php">
                    <button class='frontPagebutton'>Explore Zootopia</button>
                </a>
            </div>
        </div>
        <div class='homePageInfo'>
            <div class='featured'>
                <h1 class='featureIntro'>MEET <br> OUR <br>FEATURED <br>ANIMALS</h1>
                <div class='feature'>
                    <img class="featureimage" />
                    <h1 class="featuretitle">Red Panda</h1>
                    <span class="featuredescription">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</span>
                </div>
                <div class='feature'>
                    <img class="featureimage" />
                    <h1 class="featuretitle">Red Panda</h1>
                    <span class="featuredescription">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</span>
                </div>
                <div class='feature'>
                    <img class="featureimage" />
                    <h1 class="featuretitle">Red Panda</h1>
                    <span class="featuredescription">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</span>
                </div>
            </div>
            <div class="supportways">
                <h1 class="support-title">HOW YOU CAN SUPPORT</h1>
                <div class="support">
                    <div class="support-1">
                        <div class="donate-homepage">
                            <h1>Donate Today</h1>
                            <span>Your donation provides vital care and conservation for animals in need. Give today!</span>
                            <a href="../public/donation.php">
                                <button class="frontPageButton">Donate Now</button>
                            </a>
                        </div>
                        <div class="member-homepage">
                            <h1>Become a member</h1>
                            <span>Join Zootopia and enjoy exclusive benefits while supporting wildlife conservation.</span>
                            <a href="../public/membership.php">
                                <button class="frontPageButton">Discover Benefits</button>
                            </a>
                        </div>
                    </div>
                    <div class="support-2">
                        <div class="community-homepage">
                            <h1>Be part of our community</h1>
                            <span>Together, we can protect wildlife and create a better future for animals everywhere.</span>
                            <a href="../public/community.php">
                                <button class="frontPageButton">Join us</button>
                            </a>
                        </div>
                        <div class="adopt-homepage">
                            <h1>Adopt an animal</h1>
                            <span>Adopt an animal and help provide essential care and support for species in need.</span>
                            <a href="../public/adopt.php">
                                <button class="frontPageButton">Adopt today</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="upcoming-events-homepage">
                <h1 class="eventhome-title">UPCOMING EVENTS</h1>
                <div class="events-home">
                    <?php
                    // Loop through events and display them
                    foreach ($events as $event) {
                        $event_id = $event['event_id'];
                        $event_name = $event['event_name'];
                        $event_date = $event['event_date'];
                        $event_description = $event['description'];

                        // Fetch the image URL from get_image.php dynamically using event_id
                        $imageUrl = "../../scripts/get_event_image.php?event_id=" . $event_id;

                        // Display event with dynamic background image
                        echo "
                            <a href='/public/event.php?event_id={$event_id}' class='event-home' style='background-image: url({$imageUrl});'>
                                <div>
                                    <h1 class='event-home-name'>{$event_name}</h1>
                                    <h2 class='event-home-date'>{$event_date}</h2>
                                </div>
                                <h2 class='event-home-desc'>{$event_description}</h2>
                            </a>
                        ";
                    }
                    ?>
                </div>
            </div>
            <div class="contact-container">
                <h2>Contact Us</h2>
                <form class="form-contact" action="../scripts/contact.php" method="POST">
                    <div class="contactinfo">
                        <div>
                            <label for="firstname" class="starlabel">first name:</label>
                            <input type="text" id="firstname" name="firstname" class="starlabel" required>

                            <label for="lastname" >last name:</label>
                            <input type="text" id="lastname" name="lastname" class="starlabel" required>
                        </div>
                        <div>
                            <label for="email" class="starlabel">Email</label>
                            <input type="email" id="email" name="email" class="starlabel" required>

                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="starlabel">
                        </div>
                    </div>
                    <div class="contactMessage">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="4" class="messageArea starlabel" required></textarea>

                        <button class='frontPageButton messageButton' type="submit">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>