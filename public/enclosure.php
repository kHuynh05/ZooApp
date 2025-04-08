<head>
    <link rel="stylesheet" href="../assets/css/enclosure.css">
</head>

<?php
include '../config/database.php';

// Fetch enclosures from database
$sql = "SELECT * FROM enclosures";
$result = $conn->query($sql);
$enclosures = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $enclosures[] = $row;
    }
}

// Define enclosure descriptions and images
$enclosure_info = [
    'Safari Zone' => [
        'description' => 'A vast open space mimicking the African savanna, featuring wide grasslands and scattered trees. Perfect for our large mammals who need room to roam.',
        'image' => '../assets/img/safari-zone.jpg'
    ],
    'Canopy Heaven' => [
        'description' => 'A dense, humid environment replicating tropical rainforests. Multi-level habitats with plenty of climbing structures for our primates.',
        'image' => '../assets/img/tropical-zone.jpg'
    ],
    'Aquatic Life' => [
        'description' => 'State-of-the-art marine habitat with temperature-controlled waters and wave simulation. Ideal for our aquatic animals.',
        'image' => '../assets/img/aquatic-zone.jpg'
    ],
    'Insect World' => [
        'description' => 'A breathtaking ecosystem sheltering insects coming from all over the world, displaying trees, ant nests and more!',
        'image' => '../assets/img/Insect.jpg'
    ],
    'Reptile Realm' => [
        'description' => 'A warm, tropical environment showcasing a variety of cold-blooded creatures from deserts, rainforests, and wetlands around the world.',
        'image' => '../assets/img/Reptile.jpg'
    ],
    'Bird Domain' => [
        'description' => 'An open-air aviary and indoor exhibits featuring birds from around the globe, offering a symphony of sights and sounds as you discover the wonders of avian life.',
        'image' => '../assets/img/Brid.jpg'
    ]
    
];
?>

<div class="container">
    <?php include('../includes/navbar.php'); ?>        
    <div class="enclosures-container">
        <div class="enclosures-header">
            <h1>Our Animal Habitats</h1>
            <p>Explore our specially designed enclosures that provide natural environments for our animals</p>
        </div>

        <div class="enclosures-grid">
            <?php foreach($enclosures as $enclosure): ?>
                <div class="enclosure-card">
                        <?php 
                        // Get enclosure info or use defaults if not defined
                        $enclosure_name = $enclosure['enclosure_name'];
                        $default_image = '../assets/images/default-enclosure.jpg';
                        $default_description = 'A specially designed habitat providing a natural environment for our animals.';
                        
                        $image = isset($enclosure_info[$enclosure_name]) ? $enclosure_info[$enclosure_name]['image'] : $default_image;
                        $description = isset($enclosure_info[$enclosure_name]) ? $enclosure_info[$enclosure_name]['description'] : $default_description;
                        ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                             alt="<?php echo htmlspecialchars($enclosure_name); ?>" 
                             class="enclosure-image">
                    <div class="enclosure-content">
                        <h2><?php echo htmlspecialchars($enclosure_name); ?></h2>
                        <p><?php echo htmlspecialchars($description); ?></p>
                        <div class = "view">
                            <a href="animals_view.php?enclosure=<?php echo urlencode($enclosure['enclosure_id']); ?>" 
                                class="view-animals-btn">View Animals</a>
                                <style>
                                .enclosure-card {
                                    position: relative;
                                    padding-bottom: 60px; /* space for button */
                                }

                                .view {
                                    position: absolute;
                                    bottom: 15px;
                                    left: 15px;
                                }

                                
                                </style>
                        </div>      
                            
                        <?php

                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>