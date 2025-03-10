<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Bald Eagle</title>
    <!-- Link to your CSS file -->
    <link rel="stylesheet" href="../assets/css/animal.css" />
</head>

<body>

    <!-- Breadcrumb or back link to All Animals -->
    <?php
    include('../includes/navbar.php');
    include '../config/database.php';

    if (isset($_GET['animal_id'])) {
        $event_id = intval($_GET['animal_id']); // Convert to integer for security

        $query = "SELECT animal_name, animal_description, fact, image, species_id, animal_range, enclosure_id FROM animals WHERE animal_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $animal_id);
        $stmt->execute();
        $stmt->bind_result($animal_name, $animal_description, $fact, $image, $species_id, $animal_range, $enclosure_id);
        $stmt->fetch();

        $query = "SELECT enclosure_name FROM enclosures WHERE enclosure_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $enclosure_id);
        $stmt->execute();
        $stmt->bind_result($enclosure_name);
        $stmt->fetch();

        $query = "SELECT species_name, conservation_status, habitat FROM species WHERE species_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $species_id);
        $stmt->execute();
        $stmt->bind_result($species_name, $conservation_status, $habitat);
        $stmt->fetch();
    }

    ?>

    <!-- Hero section: large title, short description, status tabs, image -->
    <section class="hero-section">
        <div class="hero-text">
            <h1><?php echo $animal_name; ?></h1>
            <p class="subtitle">
                <?php echo $animal_description; ?>
            </p>
            <div class="status-tabs">
                <button class="active"><?php echo $conservation_status; ?></button>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo $image; ?>" alt="<?php echo $animal_name; ?>" />
        </div>
    </section>

    <!-- Facts section: white background, extra info -->
    <section class="facts-section">
        <h2>Animal Facts</h2>
        <p>
            <strong>Scientific Name</strong><br />
            <?php echo $species_name; ?>
        </p>
        <p>
            <strong>Location in the Zoo</strong><br />
            <?php echo $enclosure_name; ?>
        </p>
        <p>
            <strong>Habitat</strong><br />
            <?php echo $habitat; ?>
        </p>
        <p>
            <strong>Cool Animal Fact</strong><br />
            <?php echo $fact; ?>
        </p>
    </section>

</body>

</html>