<head>
    <link rel="stylesheet" href="../assets/css/homepage.css">
</head>
<?php
// Include database connection
include '../config/database.php';  // Make sure the path is correct

// Fetch the upcoming events (you can adjust the query based on your database)
$sqlForEvents = "SELECT event_id, event_name, event_date, description, picture FROM events ORDER BY event_date LIMIT 3";
$resultForEvents = $conn->query($sqlForEvents);

// Check if there are any events in the result
if ($resultForEvents->num_rows > 0) {
    // Loop through the results and display each event
    $events = [];
    while ($row = $resultForEvents->fetch_assoc()) {
        $events[] = $row;
    }
} else {
    // Handle the case if no events are found
    $events = [];
}

$sqlForAnimals = "SELECT animal_id, animal_name, animal_description, image FROM animals ORDER BY animal_id LIMIT 3";
$resultForAnimals = $conn->query($sqlForAnimals);

// Check if there are any animals in the result
if ($resultForAnimals->num_rows > 0) {
    // Loop through the results and display each animal
    $animals = [];
    while ($row = $resultForAnimals->fetch_assoc()) {
        $animals[] = $row;
    }
} else {
    // Handle the case if no animals are found
    $animals = [];
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
                <?php
                // Loop through animals and display them
                foreach ($animals as $animal) {
                    $animal_id = $animal['animal_id'];
                    $animal_name = $animal['animal_name'];
                    $animal_description = $animal['animal_description'];
                    $animal_image = $animal['image'];

                    // Display 3 animals
                    echo "
                            <div class='feature'>
                                <img class='featureimage' src='{$animal_image}' />
                                <h1 class='featuretitle'>{$animal_name}</h1>
                                <span class='featuredescription'>{$animal_description}</span>
                            </div>
                        ";
                }
                ?>

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
                        $event_picture = $event['picture'];


                        echo "
                            <a href='/public/event.php?event_id={$event_id}' class='event-home' style='background-image: url({$event_picture});'>
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
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>