<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user information from the database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "Error fetching user details: " . mysqli_error($conn);
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $location = $_POST['location'];

    // Process multiple image uploads
    $uploaded_images = [];
    if (!empty($_FILES['image']['name'])) { // Check if any file was uploaded
        if (is_array($_FILES['image']['name'])) {
            $image_count = count($_FILES['image']['name']);
            for ($i = 0; $i < $image_count; $i++) {
                $image_name = $_FILES['image']['name'][$i];
                $image_temp = $_FILES['image']['tmp_name'][$i];
    
                // Generate a unique filename for each image
                $unique_name = uniqid('image_') . '_' . $image_name;
                $image_path = 'uploads/' . $unique_name;
    
                // Move the uploaded image to the 'uploads' folder
                if (move_uploaded_file($image_temp, $image_path)) {
                    $uploaded_images[] = $unique_name; // Add the image name to the array
                }
            }
        } else {
            // Handle the single image case
            $image_name = $_FILES['image']['name'];
            $image_temp = $_FILES['image']['tmp_name'];
    
            // Generate a unique filename for the image
            $unique_name = uniqid('image_') . '_' . $image_name;
            $image_path = 'uploads/' . $unique_name;
    
            // Move the uploaded image to the 'uploads' folder
            if (move_uploaded_file($image_temp, $image_path)) {
                $uploaded_images[] = $unique_name; // Add the image name to the array
            }
        }
    }
    
    if (!empty($_POST['captured_image'])) {
        $capturedImageData = $_POST['captured_image'];
    
        // Extract the base64 part from the data URL
        $imageParts = explode(";base64,", $capturedImageData);
        if (count($imageParts) == 2) {
            $base64Data = $imageParts[1];
            $decodedData = base64_decode($base64Data);
    
            // Save the decoded data as an image file
            $uniqueName = uniqid('captured_') . '.png';
            $filePath = 'uploads/' . $uniqueName;
    
            if (file_put_contents($filePath, $decodedData)) {
                $uploaded_images[] = $uniqueName;
            }
        }
    }
    
    // If images were uploaded, save the filenames as a comma-separated string
    $image_list = implode(',', $uploaded_images);

    
    // Get coordinates from the form
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Validate latitude and longitude
    if (empty($latitude) || empty($longitude)) {
        echo "<script>
                alert('Error: Latitude and longitude must be provided.');
                window.history.back();
              </script>";
        exit();
    }

    // Insert item into the database
$query = "INSERT INTO items (user_id, title, description, location, type, image, latitude, longitude) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

// Bind parameters - ensure types match the data
$stmt->bind_param("ssssssdd", $user_id, $title, $description, $location, $type, $image_list, $latitude, $longitude);

// Execute the statement
if ($stmt->execute()) {
// Get the ID of the newly inserted item
$item_id = $stmt->insert_id;

// Define the message for the notification
$message = "Your item '$title' has been successfully posted!";

// Define notification type (e.g., 'post' since it's related to posting an item)
$notification_type = 'post';

// Define sender_id (assuming the sender is the logged-in user, you may need to adjust this depending on your app's logic)
$sender_id = $user_id; 

// Insert notification for the user
$notif_query = "INSERT INTO notifications (user_id, sender_id, item_id, notification_type, message) VALUES (?, ?, ?, ?, ?)";
$notif_stmt = $conn->prepare($notif_query);

// Bind parameters with correct data types
$notif_stmt->bind_param("iiiss", $user_id, $sender_id, $item_id, $notification_type, $message);

// Execute the query
if ($notif_stmt->execute()) {
echo "Item posted successfully!";
header('Location: profile.php'); // Redirect to profile page after successful post
} else {
echo "Error inserting notification: " . $notif_stmt->error;
}

$notif_stmt->close();
} else {
echo "Error posting item: " . $stmt->error;
}

$stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Item</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/post_item.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include('web_navbar.php'); ?>
<main class="form-container">
    <form action="post_item.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="Enter the title (e.g., Lost Pet, Missing Item, Found Person)" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" placeholder="Provide a detailed description (e.g., item features, appearance, or circumstances)" required></textarea>

        <label for="type">Type:</label>
        <select id="type" name="type" required>
            <option value="Lost">Lost</option>
            <option value="Found">Found</option>
        </select>

        <label for="location">Location:</label>
        <div class= "location-input">
        <input type="text" id="location" name="location" placeholder="Type or pin the location on the map">
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
  
        </div>
        <div id="map"></div><br>

        <!-- Image Upload -->
        <div class="camera-container">
        <label for="image">Image:</label>
        <input type="file" id="image" name="image[]" accept="image/*" multiple>
        
        <!-- Camera Upload -->
        <button type="button" id="start-camera" class="camera-button">
            <i class="fas fa-camera"></i>
        </button>
           
        </div>
        <div id="camera-dropdown" class="camera-dropdown">
    <p style="font-size: 12px; font-weight: bold; color: red; text-align: center; margin: 20px;">Please ensure that your camera is enabled to use the capture feature. If you haven't already, grant the necessary permissions for the camera to function.</p>
    <video id="video" autoplay></video><br>
    <canvas id="canvas"></canvas>
    
    <!-- Add a container for the buttons -->
    <div class="button-container">
        <button type="button" id="capture" class="capture-button">
            <i class="fas fa-circle-notch"></i> 
        </button>
        <button type="button" id="retake" class="retake-button">
            <i class="fas fa-sync-alt"></i> 
        </button>
    </div>
    <br>
    <div id="preview"></div>
    <input type="hidden" id="captured-image" name="captured_image">
    </div>
        <br>
        <div class="button-container">
            <button type="submit" name="submit" id="post-item" class="post-button">Post Item</button>
            <button type="button" id="cancel" class="cancel-button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='dashboard.php';">Cancel</button>
        </div>

</main>
</form>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="assets/js/forms.js"></script>
</body>

<?php
include 'includes/footer.php';
?>

</html>
