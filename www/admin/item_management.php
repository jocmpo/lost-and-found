<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../auth.php");
    exit();
}

// Include database connection
include('../includes/db.php');

// Handle search and filtering
$search = '';
$type_filter = '';

// Check if the search query or type filter is set
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (isset($_GET['type'])) {
    $type_filter = mysqli_real_escape_string($conn, $_GET['type']);
}
$query = "SELECT items.*, users.name AS poster_name, items.created_at 
          FROM items 
          LEFT JOIN users ON items.user_id = users.id 
          WHERE 1";

// Modify the query based on search and filter
$query = "SELECT items.*, users.name AS poster_name 
          FROM items 
          LEFT JOIN users ON items.user_id = users.id 
          WHERE 1";

if ($search != '') {
    $query .= " AND (items.title LIKE '%$search%' OR items.description LIKE '%$search%' OR items.location LIKE '%$search%')";
}

if ($type_filter != '') {
    $query .= " AND items.type = '$type_filter'";
}

$query .= " ORDER BY items.created_at DESC";
$result = mysqli_query($conn, $query);

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM items WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $item_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Item successfully deleted.";
    } else {
        $_SESSION['error'] = "Failed to delete item: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
    header('Location: item_management.php');
    exit();
}

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
    header("Location: item_management.php");
    exit();
}
include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Records Management</title>
</head>
<body>


<div class="dashboard-container">
    <h1>Records Management</h1>

    <!-- Search and Filter Section -->
    <form method="get" action="item_management.php" class="item-management-form">
    <input type="text" name="search" placeholder="Search by title, description, or location" value="<?php echo htmlspecialchars($search); ?>">
    <select name="type">
        <option value="">All Status</option>
        <option value="Lost" <?php echo ($type_filter == 'Lost') ? 'selected' : ''; ?>>Lost</option>
        <option value="Found" <?php echo ($type_filter == 'Found') ? 'selected' : ''; ?>>Found</option>
        <option value="Claimed" <?php echo ($type_filter == 'Claimed') ? 'selected' : ''; ?>>Claimed</option>
    </select>
    <button type="submit">Search</button>
    <a href="item_management.php" class="reset-btn">Reset</a>

</form>
    <?php if (isset($_SESSION['success'])): ?>
    <p class="success" id="successMessage"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error" id="errorMessage"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Location</th>
                <th>Image</th>
                <th>Posted By</th>
                <th>Date Posted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['type']); ?></td>
                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                    <td>
                    <?php 
                        $images = explode(',', $item['image']); 
                        $firstImage = trim($images[0]);

                        if (!empty($firstImage)) {
                    ?>
                        <img src="../uploads/<?php echo htmlspecialchars($firstImage); ?>" 
                            onerror="this.onerror=null;this.src='../css/img/no-pictures.png'; this.classList.add('fallback-image');" 
                            class="zoom-image" 
                            style="max-width: 150px; height: auto;" />
                    <?php 
                        } else {
                    ?>
                        <img src="../css/img/no-pictures.png" 
                            class="zoom-image fallback-image" 
                            style="max-width: 150px; height: auto;" />
                    <?php 
                        }
                    ?>
                    </td>

                    </td>
                    <td><?php echo htmlspecialchars($item['poster_name']); ?></td>
                    <td><?php echo date("F d, Y h:i A", strtotime($item['created_at'])); ?></td>
                    <td>
                    <a href="javascript:void(0);"
                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item['id']), ENT_QUOTES, 'UTF-8'); ?>, 
                                                    <?php echo htmlspecialchars(json_encode($item['title']), ENT_QUOTES, 'UTF-8'); ?>, 
                                                    <?php echo htmlspecialchars(json_encode($item['description']), ENT_QUOTES, 'UTF-8'); ?>, 
                                                    <?php echo htmlspecialchars(json_encode($item['type']), ENT_QUOTES, 'UTF-8'); ?>, 
                                                    <?php echo htmlspecialchars(json_encode($item['location']), ENT_QUOTES, 'UTF-8'); ?>, 
                                                    <?php echo htmlspecialchars(json_encode($item['image']), ENT_QUOTES, 'UTF-8'); ?>);">
                                <img src="../css/img/edit.png" alt="Edit" width="20" height="20">
                            </a>

                        <a href="?delete=<?php echo $item['id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this item?');">
                            <img src="../css/img/trash.png" alt="Delete" width="20" height="20">
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Item Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Item</h2>
        <form id="editItemForm" action="item_management.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="itemId" name="item_id">
            
            <label for="itemTitle">Title:</label>
            <input type="text" id="itemTitle" name="title" required>
            <br><br>

            <label for="itemDescription">Description:</label>
            <textarea id="itemDescription" name="description" required></textarea>
            <br><br>

            <label for="itemType">Type:</label>
            <select id="itemType" name="type" required>
                <option value="Lost">Lost</option>
                <option value="Found">Found</option>
                <option value="Claimed">Claimed</option>
            </select>
            <br><br>

            <label for="itemLocation">Location:</label>
            <input type="text" id="itemLocation" name="location" required>
            <br><br>

            <label for="itemImage">Image:</label>
            <input type="file" id="itemImage" name="image" accept="image/*">
            <br><br>
           <div id="currentImageContainer">
    <label>Current Image:</label>
    <?php 
        if (!empty($item['image'])) {
            $images = explode(',', $item['image']); 
            
            if (!empty($images[0])): 
    ?>
                <img src="../uploads/<?php echo htmlspecialchars($images[0]); ?>" 
                    onerror="this.onerror=null;this.src='../css/img/no-pictures.png'; this.classList.add('fallback-image');" 
                    class="zoom-image" 
                    style="max-width: 150px; height: auto;" 
                    onclick="updateCurrentImage(this.src);" /> 
            <?php endif; ?>
        <?php } ?>

    <img id="currentImage" src="" alt="Current Item Image" width="80" />
</div>


            <input type="hidden" id="itemLatitude" name="latitude">
            <input type="hidden" id="itemLongitude" name="longitude">

            <input type="submit" name="update_item" value="Update Item">
        </form>
    </div>
</div>

<script>
// Function to open the modal and populate form data
function openEditModal(itemId, title, description, type, location, image) {
    document.getElementById("editModal").style.display = "block";
    document.getElementById("itemId").value = itemId;
    document.getElementById("itemTitle").value = title;
    document.getElementById("itemDescription").value = description;
    document.getElementById("itemType").value = type;
    document.getElementById("itemLocation").value = location;

    // Reset current image display
    let currentImage = document.getElementById("currentImage");
    currentImage.style.display = "none"; 

    if (image) {
        let imageList = image.split(','); // If multiple images exist, take the first one
        let firstImage = imageList[0].trim(); // Trim spaces to avoid broken links

        if (firstImage) {
            currentImage.src = "../uploads/" + firstImage;
            currentImage.style.display = "block";
        }
    }
}

// Close the modal
function closeModal() {
    document.getElementById("editModal").style.display = "none";
}

setTimeout(function() {
        var successMessage = document.getElementById('successMessage');
        var errorMessage = document.getElementById('errorMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 2000); 
    function updateCurrentImage(imageSrc) {
    const currentImage = document.getElementById('currentImage');
    currentImage.src = imageSrc;  // Set the src of the #currentImage to the clicked image
}
</script>
</body>
</html>
