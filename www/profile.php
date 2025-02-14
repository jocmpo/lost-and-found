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

$logged_in_user_id = $_SESSION['user_id'];

// Check if a user_id is passed in the URL to display another user's profile
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $logged_in_user_id;

// Fetch the profile info (including name, photo, etc.) for the user whose items we want to display
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// If no user found, redirect to the logged-in user's profile
if (!$user) {
    header('Location: profile.php');
    exit();
}
// Check if delete_item_id is passed in the URL and delete the item
if (isset($_GET['delete_item_id'])) {
    // Sanitize the delete_item_id to prevent SQL injection
    $delete_item_id = intval($_GET['delete_item_id']);
    
    // Prepare the query to delete the item
    $delete_query = "DELETE FROM items WHERE id = ? AND user_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "ii", $delete_item_id, $logged_in_user_id);

    // Execute the query
    if (mysqli_stmt_execute($delete_stmt)) {
        // Redirect to the profile page after successful deletion
        header("Location: profile.php?message=Item deleted successfully");
        exit();
    } else {
        echo "Error deleting item.";
    }

    // Clean up the prepared statement
    mysqli_stmt_close($delete_stmt);
}
// Fetch profile photo or use default
$profile_photo = isset($user['photo']) && !empty($user['photo']) ? '' . $user['photo'] : 'css/img/user.png';

// If a new photo is uploaded, handle the upload
if (isset($_FILES['photo'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate the image file (optional)
    if (getimagesize($_FILES["photo"]["tmp_name"]) !== false) {
        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            // Save the filename to the database
            $photo = basename($_FILES["photo"]["name"]);
            $update_query = "UPDATE users SET photo = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $photo, $user_id);
            mysqli_stmt_execute($stmt);

            // Update the profile photo to reflect the change immediately
            $profile_photo = $photo;
        } else {
            echo "Error uploading the file.";
        }
    } else {
        echo "File is not an image.";
    }
}

// Fetch the items for the user (either logged-in user or another user)
$item_query = "SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC"; 
$item_stmt = mysqli_prepare($conn, $item_query);
mysqli_stmt_bind_param($item_stmt, "i", $user_id);
mysqli_stmt_execute($item_stmt);
$item_result = mysqli_stmt_get_result($item_stmt);

?>
<?php include 'web_navbar.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?>'s Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/no_message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <main class="container">
    <section class="user-profile">
    <?php if ($logged_in_user_id == $user['id']): ?>
    <a href="profile_settings.php" class="settings-icon">
        <i class="fas fa-cogs"></i>
    </a>
    <?php endif; ?>

    <div class="profile-header">
        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" class="pp">
        <div class="profile-info">
            <h3 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h3>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if ($logged_in_user_id != $user['id']): ?>
                <p><a href="message.php?user_id=<?php echo $user['id']; ?>&message=Hi" class="chat-link"><i class="fas fa-comment"></i> Chat</a></p>
                <?php endif; ?>
        </div>
    </div>
    
    </section>
        <div class="profile-container">
            <?php if ($item_result && mysqli_num_rows($item_result) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($item_result)): ?>
                    <div class="item-card">
                        
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p>
                            <span class="status <?php echo strtolower(htmlspecialchars($item['type'])); ?>">
                                <?php echo htmlspecialchars($item['type']); ?>
                            </span>
                        </p>
                        <p><?php echo htmlspecialchars($item['description']); ?> <a href="item_detail.php?item_id=<?php echo $item['id']; ?>" class="item-link">
                                View More...
                            </a></p>
                        
                        <p><strong><i class="fas fa-map-marker-alt"></i></strong>
                        <a href="https://www.google.com/maps?q=<?php echo urlencode(htmlspecialchars($item['location'])); ?>" 
                        target="_blank">
                            <?php echo htmlspecialchars($item['location']); ?>
                        </a></p>
                        <p><strong><i class="fas fa-calendar"></i></strong> <?php echo date("F j, Y h:i A", strtotime($item['created_at'])); ?></p>
                        <?php 
                           
                            $images = explode(',', $item['image']);
                            $first_image = $images[0]; // Get the first image in the list
                            ?>
                            <img src="uploads/<?php echo htmlspecialchars($first_image); ?>" alt="Item Image" class="item-img" onerror="this.onerror=null;this.src='css/img/no-pictures.png'; this.classList.add('fallback-image');" />
                        <?php if ($logged_in_user_id == $user['id']): ?>
                            <div class="item-actions">
                                <a href="edit_item.php?item_id=<?php echo $item['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="profile.php?delete_item_id=<?php echo $item['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                
                            </div>
               
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-items-message">This user has no posted items.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Structure -->
<div id="imageModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImage">
    <div id="caption"></div>
</div>

</body>
<script>
    // Get modal elements
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImage");
    var captionText = document.getElementById("caption");

    // Get all images in the profile-container
    var images = document.querySelectorAll(".profile-container img");

    // Loop through each image to add click event
    images.forEach(function (img) {
        img.addEventListener("click", function () {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        });
    });

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    };
</script>

<?php
include 'includes/footer.php';
?>

<?php 
// Clean up prepared statements
mysqli_stmt_close($item_stmt);
mysqli_stmt_close($user_stmt);
?>
