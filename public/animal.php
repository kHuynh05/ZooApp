<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <!-- Link to your CSS file -->
    <link rel="stylesheet" href="../assets/css/animal.css" />
</head>

<body>

    <!-- Breadcrumb or back link to All Animals -->
    <?php
    include('../includes/navbar.php');
    include '../config/database.php';

    if (isset($_GET['species_id'])) {
        $species_id = intval($_GET['species_id']);

        $query = "SELECT s.species_name, s.img, s.description, s.fun_fact, s.conservation_status, s.habitat,
                 e.enclosure_name
          FROM species AS s, enclosures AS e
          WHERE s.enclosure_id = e.enclosure_id AND s.species_id = ?";


        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $species_id);
        $stmt->execute();
        $stmt->bind_result(
            $species_name,
            $img,
            $description,
            $fun_fact,
            $conservation_status,
            $habitat,
            $enclosure_name
        );
        $stmt->fetch();
        $stmt->close();

        if ($species_name) {
            echo '<section class="hero-section">';
            echo '<div class="hero-text">';
            echo '<h1>' . $species_name . '</h1>';
            echo '<p class="subtitle">' . $description . '</p>';
            echo '<div class="status-tabs">';
            echo '<button class="' . (strtolower($conservation_status) === 'stable' ? 'active' : '') . ' stable">Stable</button>';
            echo '<button class="' . (strtolower($conservation_status) === 'vulnerable' ? 'active' : '') . ' vulnerable">Vulnerable</button>';
            echo '<button class="' . (strtolower($conservation_status) === 'endangered' ? 'active' : '') . ' endangered">Endangered</button>';
            echo '</div>';
            echo '</div>';
            echo '<div class="hero-image">';
            echo '<img src="' . $img . '" alt="' . $species_name . '" />';
            echo '</div>';
            echo '</section>';

            echo '<section class="facts-section">';
            echo '<h2>Animal Facts</h2>';
            echo '<p><strong>Scientific Name</strong><br />' . $species_name . '</p>';
            echo '<p><strong>Location in the Zoo</strong><br />' . $enclosure_name . '</p>';
            echo '<p><strong>Habitat</strong><br />' . $habitat . '</p>';
            echo '<p><strong>Cool Animal Fact</strong><br />' . $fun_fact . '</p>';
            echo '</section>';
        } else {
            echo "<p>Animal not found.</p>";
        }
        $conn->close();
    } else {
        echo "<p>No species ID provided.</p>";
    }
    ?>
    <?php include('../includes/footer.php'); ?>


</body>

</html>