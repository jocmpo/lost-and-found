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

// Check if the item ID is passed for editing
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    // Fetch the existing item from the database
    $query = "SELECT * FROM items WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $item_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
    } else {
        echo "Item not found or you do not have permission to edit this item.";
        exit();
    }

} else {
    echo "No item specified for editing.";
    exit();
}

// Handle form submission for updating the item
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $location = $_POST['location'];
    
    // We'll collect new images here.
    $uploaded_images = [];

    // Process multiple file uploads
    if (!empty($_FILES['image']['name'][0])) { // Check if at least one file was uploaded
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
    }

    // Process captured image from the camera (base64 encoded)
    if (!empty($_POST['captured_image'])) {
        $capturedImageData = $_POST['captured_image'];

        // Remove the base64 header (e.g., "data:image/png;base64,")
        $imageParts = explode(";base64,", $capturedImageData);
        if (count($imageParts) == 2) {
            $base64Data = $imageParts[1];
            $decodedData = base64_decode($base64Data);

            // Generate a unique filename for the captured image
            $unique_name = uniqid('camera_image_') . '.png';
            $filePath = 'uploads/' . $unique_name;

            if (file_put_contents($filePath, $decodedData)) {
                $uploaded_images[] = $unique_name;
            }
        }
    }

    // Determine what to store in the database:
    // If no new images were uploaded, then keep the existing images.
    // Otherwise, combine new images with the existing images (if desired).
    if (empty($uploaded_images)) {
        // No new images uploaded, so keep the existing image data.
        $image_list = $item['image'];
    } else {
        // If you want to append the new images to the existing ones:
        if (!empty($item['image'])) {
            // Combine existing images (already stored as a comma-separated string)
            // with the newly uploaded images.
            $image_list = $item['image'] . ',' . implode(',', $uploaded_images);
        } else {
            // Only new images exist.
            $image_list = implode(',', $uploaded_images);
        }
    }

    $update_query = "UPDATE items SET title = ?, description = ?, location = ?, type = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssi", $title, $description, $location, $type, $image_list, $item['id']);

    if ($stmt->execute()) {
        echo "Item updated successfully!";
        header('Location: profile.php'); // Redirect after successful update
        exit();
    } else {
        echo "Error updating item: " . $stmt->error;
    }

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

    // Update the item in the database
    $query = "UPDATE items SET title = ?, description = ?, location = ?, type = ?, image = ?, latitude = ?, longitude = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssddi", $title, $description, $location, $type, $image, $latitude, $longitude, $item_id, $user_id);

    if ($stmt->execute()) {
        // Insert notification for the user
        $message = "Your item '$title' has been successfully updated!";
        $notif_query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param("is", $user_id, $message);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo "Item updated successfully!";
        header('Location: profile.php'); // Redirect to search page after successful update
    } else {
        echo "Error updating item: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/post_item.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include('web_navbar.php'); ?>
<main class="form-container">
    <form action="edit_item.php?item_id=<?php echo $item_id; ?>" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo $item['title']; ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo $item['description']; ?></textarea>

        <label for="type">Type:</label>
        <select id="type" name="type" required>
            <option value="Lost" <?php echo $item['type'] == 'Lost' ? 'selected' : ''; ?>>Lost</option>
            <option value="Found" <?php echo $item['type'] == 'Found' ? 'selected' : ''; ?>>Found</option>
            <option value="Claimed" <?php if ($item['type'] == 'Claimed') echo 'selected'; ?>>Claimed</option>
        </select>

        <label for="location">Location:</label>
        <div class= "location-input">
        <input type="text" id="location" name="location" value="<?php echo $item['location']; ?>">
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

    </form>
</main>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="assets/js/forms.js"></script>

</body>
</html>
