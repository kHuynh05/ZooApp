<head>
    <link rel="stylesheet" href="../assets/css/enclosure.css">
    <style>
        .enclosure-card {
            position: relative;
            padding-bottom: 60px; /* space for button */
        }

        .inner-status{
            padding: 15px 25px;
            font-weight: bold;
            border-radius: 50px;
            border: 2px solid;
            background-color: transparent;
            opacity: 0.5;
            transition: all 0.3s ease;
            cursor: default;
            pointer-events: none;
        }

        .inner-view{
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 115px;
        }

        .view {
            position: absolute;
            bottom: 15px;
            left: 15px;
            gap: 500px;
        }

        .inner-status {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            border: none;
        }

        .status-open {
            background-color: rgba(40, 167, 69, 0.1);
            border-color: #28a745;
            color: #28a745;
        }

        .status-close {
            border-color: #dc3545;
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        
   </style>
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
$enclosure_info = [];

foreach ($enclosures as $enclosure) {
    $name = $enclosure['enclosure_name'];
    $enclosure_info[$name] = [
        'description' => $enclosure['enclosure_desc'],
        'image' => $enclosure['img'],
        'enc_status' => $enclosure['status']
    ];
}
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
                        $enc_status = isset($enclosure_info[$enclosure_name]) ? $enclosure_info[$enclosure_name]['enc_status'] : $default_status;
                        ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                             alt="<?php echo htmlspecialchars($enclosure_name); ?>" 
                             class="enclosure-image">
                    <div class="enclosure-content">
                        <h2 class = "inner-name"><?php echo htmlspecialchars($enclosure_name); ?></h2>
                        <p><?php echo htmlspecialchars($description); ?></p>
                        <div class = "view">
                            <div class = "inner-view">
                                <a href="animals_view.php?enclosure=<?php echo urlencode($enclosure['enclosure_id']); ?>" 
                                        class="view-animals-btn">View Animals</a>
                                <?php 
                                $status = strtolower($enclosure['status']);
                                $status_class = $status === 'open' ? 'status-open' : 'status-close';
                                ?>

                                <button class="inner-status <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($enclosure['status']); ?>
                                </button>
                            </div>
                        </div>      
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</div>