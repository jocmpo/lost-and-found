<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../auth.php");
    exit();
}

include('../includes/db.php');
$photo = '../assets/img/user.png';  // Default photo path

// Fetch the current profile picture from the database
$query = "SELECT photo FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($db_picture);

if ($stmt->fetch() && $db_picture) {
    $photo = $db_picture;  // Use the photo from the database if available
}

$stmt->close();

// Query to get total number of users
$query_users = "SELECT COUNT(*) AS total_users FROM users";
$result_users = mysqli_query($conn, $query_users);
$total_users = mysqli_fetch_assoc($result_users)['total_users'];

// Query to get total number of lost items
$query_lost = "SELECT COUNT(*) AS total_lost FROM items WHERE type = 'Lost'";
$result_lost = mysqli_query($conn, $query_lost);
$total_lost = mysqli_fetch_assoc($result_lost)['total_lost'];

// Query to get total number of found items
$query_found = "SELECT COUNT(*) AS total_found FROM items WHERE type = 'Found'";
$result_found = mysqli_query($conn, $query_found);
$total_found = mysqli_fetch_assoc($result_found)['total_found'];

// Query to get total number of claimed items
$query_claimed = "SELECT COUNT(*) AS total_claimed FROM items WHERE type = 'Claimed'";
$result_claimed = mysqli_query($conn, $query_claimed);
$total_claimed = mysqli_fetch_assoc($result_claimed)['total_claimed'];

// Query to get place with the most missing items
$query_place = "SELECT location, COUNT(*) AS missing_items_count FROM items WHERE type = 'Lost' GROUP BY location ORDER BY missing_items_count DESC LIMIT 1";
$result_place = mysqli_query($conn, $query_place);
$place_data = mysqli_fetch_assoc($result_place);

// Ensure $place_data['location'] is set and not null before splitting
if (isset($place_data['location']) && !empty($place_data['location'])) {
    $location_parts = explode(",", $place_data['location']); // Split by commas
    $place_with_most_missing = isset($location_parts[2]) ? trim($location_parts[2]) : 'Unknown'; // Fallback to 'Unknown' if there's no third element
} else {
    $place_with_most_missing = 'Unknown';
}

// Fetch the count of missing items
$missing_items_count = isset($place_data['missing_items_count']) ? $place_data['missing_items_count'] : 0;


// Query to get the number of users who signed up on each day of the last month
$query_month = "
    SELECT 
        DATE(created_at) AS sign_up_date,
        COUNT(*) AS total_signups
    FROM users
    WHERE created_at >= NOW() - INTERVAL 1 MONTH
    GROUP BY DATE(created_at)
    ORDER BY sign_up_date ASC
";
$result_month = mysqli_query($conn, $query_month);

// Fetch the results into an array for the chart
$days = [];
$signups = [];

while ($row = mysqli_fetch_assoc($result_month)) {
    $days[] = $row['sign_up_date'];
    $signups[] = $row['total_signups'];
}

// Prepare chart data in JSON format to pass to JavaScript
$chart_data = json_encode([
    'days' => $days,
    'signups' => $signups
]);

// Handle item update
if (isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $location = $_POST['location'];
    $image = $_FILES['image']['name'];

    // Handle image upload (if there is a new image)
    if (!empty($image)) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } else {
        $image = $_POST['current_image'];  // Keep the old image if no new one
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['image']['type'], $allowedTypes)) {
        // proceed with upload
    } else {
        $_SESSION['error'] = "Invalid image file type.";
    }
    
    // Update item in the database
    $update_query = "UPDATE items SET title=?, description=?, type=?, location=?, image=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $type, $location, $image, $item_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Item updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update item: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
}
include('sidebar.php');
?>
<?php
// Query to get the top 5 places with the most missing items
$query_top_places = "
    SELECT location, COUNT(*) AS missing_items_count 
    FROM items 
    WHERE type = 'Lost' 
    GROUP BY location 
    ORDER BY missing_items_count DESC 
    LIMIT 5
";
$result_top_places = mysqli_query($conn, $query_top_places);

// Create an array to store the places with the missing items count
$top_places = [];
while ($row = mysqli_fetch_assoc($result_top_places)) {
    $location_parts = explode(",", $row['location']); // Split by commas
    $place_name = isset($location_parts[2]) ? trim($location_parts[2]) : 'Unknown'; // Default to 'Unknown' if no third element
    $top_places[] = [
        'location' => $place_name,
        'missing_items_count' => $row['missing_items_count']
    ];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>

    <!-- Main Content -->
    <div class="dashboard-container">
        <h1>Welcome, Admin</h1>

<!-- Stats Section -->
<div class="stats">
    <div class="stat">
        <a href="user_management.php">
            <img src="../css/img/user_1.gif" alt="User GIF" class="stat-gif">
            <h3>Users</h3>
            <p><?php echo $total_users; ?></p>
        </a>
    </div>
    <div class="stat">
        <a href="item_management.php">
            <img src="../css/img/search_1.gif" alt="Search GIF" class="stat-gif">
            <h3>Lost</h3>
            <p><?php echo $total_lost; ?></p>
        </a>
    </div>
    <div class="stat">
        <a href="item_management.php">
            <img src="../css/img/binocular_1.gif" alt="Found GIF" class="stat-gif">
            <h3>Found</h3>
            <p><?php echo $total_found; ?></p>
        </a>
    </div>
    <div class="stat">
        <a href="item_management.php">
            <img src="../css/img/handshake_1.gif" alt="Claimed GIF" class="stat-gif">
            <h3>Claimed</h3>
            <p><?php echo $total_claimed; ?></p>
        </a>
    </div>
    <div class="stat">
        <a href="item_management.php">
            <img src="../css/img/location_pin.gif" alt="Location GIF" class="stat-gif">
            <h3 style="font-size: 15px;">Highest Missing Reports</h3>
            <p>
            <?php echo $place_with_most_missing; ?></p>
        </a>
    </div>
</div>

        <!-- Chart Section -->
<div class="chart-container">
                <div class="analytics-reporting">
            <div class="card">
                <h2 class="title">Recent User Registrations</h2>
                <div class="chart-container">
                    <canvas id="itemsChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Top 5 Places with Highest Missing Items Section -->
        <div class="top-places-section">
            <h3>Top 5 Places with Highest Missing Items</h3>
            <ul class="top-places-list">
                <?php foreach ($top_places as $place): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($place['location']); ?>:</strong> 
                        <?php echo $place['missing_items_count']; ?> missing items
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="view-more-container">
                <a href="item_management.php" class="view-more-btn">View More</a>
            </div>
        </div>

</div>
    <script>
        // Fetch chart data from PHP (via the PHP variable $chart_data)
        const chartData = <?php echo $chart_data; ?>;

        // Create chart data
        const data = {
            labels: chartData.days, // X-axis: The days of the last month
            datasets: [
                {
                    label: 'Number of Signups',
                    data: chartData.signups, // Y-axis: Total signups on each day
                    borderColor: '#256EBB',
                    backgroundColor: 'rgba(19, 48, 79, 0.2)',
                    borderWidth: 2
                }
            ]
        };

    
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' signups';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: ''
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Signups'
                        }
                    }
                }
            }
        };

        // Render chart
        const ctx = document.getElementById('itemsChart').getContext('2d');
        new Chart(ctx, config);
    </script>
</body>
</html>
