<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to login page if not an admin
    header("Location: ../auth.php");
    exit();
}

// Admin-specific functionality here (like managing users, settings, etc.)
include('../includes/db.php');

// Handle search and filtering
$search = '';
$sender_filter = '';
$recipient_filter = '';

// Check if search and filter parameters are set
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (isset($_GET['sender'])) {
    $sender_filter = mysqli_real_escape_string($conn, $_GET['sender']);
}

if (isset($_GET['recipient'])) {
    $recipient_filter = mysqli_real_escape_string($conn, $_GET['recipient']);
}

// Base query for messages
$message_query = "SELECT m.id, m.sender_id, m.recipient_id, m.message, m.timestamp, m.image, u1.name AS sender_name, u2.name AS recipient_name
                  FROM messages m
                  JOIN users u1 ON u1.id = m.sender_id
                  JOIN users u2 ON u2.id = m.recipient_id
                  WHERE 1";

// Apply search filter
if ($search != '') {
    $message_query .= " AND (m.message LIKE '%$search%' OR u1.name LIKE '%$search%' OR u2.name LIKE '%$search%')";
}

// Apply sender filter
if ($sender_filter != '') {
    $message_query .= " AND m.sender_id = '$sender_filter'";
}

// Apply recipient filter
if ($recipient_filter != '') {
    $message_query .= " AND m.recipient_id = '$recipient_filter'";
}

// Order by timestamp
$message_query .= " ORDER BY m.timestamp DESC";

// Execute query
$message_result = mysqli_query($conn, $message_query);

// Handle message deletion
if (isset($_GET['delete_message_id'])) {
    $message_id = $_GET['delete_message_id'];
    $delete_message_query = "DELETE FROM messages WHERE id = '$message_id'";
    mysqli_query($conn, $delete_message_query);
    header("Location: chat_management.php");
    exit();
}

// Handle image upload and message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Process the uploaded image
        $target_dir = "uploads/";  // Directory for uploaded files
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);  
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Image uploaded successfully, now insert it into the database
                $message = mysqli_real_escape_string($conn, $_POST['message']);
                $sender_id = $_SESSION['user_id']; // Assuming the sender is logged in
                $recipient_id = $_POST['recipient_id']; // Replace with actual recipient ID from your form

                $insert_message_query = "INSERT INTO messages (sender_id, recipient_id, message, image, timestamp) 
                                         VALUES ('$sender_id', '$recipient_id', '$message', '$target_file', NOW())";
                mysqli_query($conn, $insert_message_query);

                // Redirect or confirm message submission
                header("Location: chat_management.php");
                exit();
            } else {
                // Error uploading the image
                echo "Failed to upload the image.";
            }
        } else {
            echo "The uploaded file is not a valid image.";
        }
    } else {
        // No image uploaded or there was an error
        $imagePath = null;
    }
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
    <title>Message Management</title>
</head>
<body>

    <div class="dashboard-container">
        <h1>Manage All Messages</h1>
        
        <form method="GET" action="chat_management.php" class="message-management-form">
            <input type="text" name="search" placeholder="Search messages, sender, recipient" value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="sender">
                <option value="">Filter by Sender</option>
                <?php
                $senders = mysqli_query($conn, "SELECT id, name FROM users");
                while ($sender = mysqli_fetch_assoc($senders)) {
                    $selected = ($sender['id'] == $sender_filter) ? 'selected' : '';
                    echo "<option value='{$sender['id']}' $selected>{$sender['name']}</option>";
                }
                ?>
            </select>
            
            <select name="recipient">
                <option value="">Filter by Recipient</option>
                <?php
                $recipients = mysqli_query($conn, "SELECT id, name FROM users");
                while ($recipient = mysqli_fetch_assoc($recipients)) {
                    $selected = ($recipient['id'] == $recipient_filter) ? 'selected' : '';
                    echo "<option value='{$recipient['id']}' $selected>{$recipient['name']}</option>";
                }
                ?>
            </select>
            <button type="submit" class="search-btn">Search</button>
            <a href="chat_management.php" class="reset-btn">Reset</a>
        </form>
        
        <div class="message-list">
            <?php while ($message = mysqli_fetch_assoc($message_result)) { ?>
                <div class="message">
                    <div class="message-header">
                        <span>From: <?php echo htmlspecialchars($message['sender_name']); ?></span>
                        <span>To: <?php echo htmlspecialchars($message['recipient_name']); ?></span>
                    </div>
                    <div class="message-body">
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                        <?php if (!empty($message['image'])): ?>
                            <img src="../uploads/<?php echo basename($message['image']); ?>" alt="Uploaded Image" style="max-width: 150px; height: auto;">
                        <?php endif; ?>

                    </div>
                    <div class="message-footer">
                        <small><?php echo date("F j, Y, g:i a", strtotime($message['timestamp'])); ?></small>
                        <a href="chat_management.php?delete_message_id=<?php echo $message['id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this message?');">
                            <img src="../css/img/trash.png" alt="Delete" width="20" height="20"/>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
