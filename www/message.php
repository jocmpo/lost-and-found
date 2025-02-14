<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$logged_in_user_id = $_SESSION['user_id'];

// Fetch the search term from GET request (if any)
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Modify the query to filter based on the search term
$user_query = "SELECT DISTINCT u.id, u.name FROM users u 
               JOIN messages m ON (m.sender_id = u.id AND m.recipient_id = '$logged_in_user_id') 
               OR (m.sender_id = '$logged_in_user_id' AND m.recipient_id = u.id)
               WHERE u.id != '$logged_in_user_id'";

if ($search_term) {
    $user_query .= " AND u.name LIKE '%$search_term%'";
}

$user_result = mysqli_query($conn, $user_query);

// Check if we are in chat mode (if a recipient ID is provided)
if (isset($_GET['user_id'])) {
    $recipient_id = $_GET['user_id'];

    // Fetch recipient data
    $recipient_query = "SELECT * FROM users WHERE id = '$recipient_id'";
    $recipient_result = mysqli_query($conn, $recipient_query);
    if ($recipient_result && mysqli_num_rows($recipient_result) > 0) {
        $recipient = mysqli_fetch_assoc($recipient_result);
    } else {
        echo "Recipient not found.";
        exit();
    }

    // Fetch sender data (to use sender's name in notification)
    $sender_query = "SELECT * FROM users WHERE id = '$logged_in_user_id'";
    $sender_result = mysqli_query($conn, $sender_query);
    if ($sender_result && mysqli_num_rows($sender_result) > 0) {
        $sender = mysqli_fetch_assoc($sender_result);
    } else {
        echo "Sender not found.";
        exit();
    }

    $profile_photo = isset($recipient['photo']) && !empty($recipient['photo']) ? $recipient['photo'] : 'css/img/user.png';


    // Fetch the chat history
    $message_query = "SELECT * FROM messages WHERE (sender_id = '$logged_in_user_id' AND recipient_id = '$recipient_id') 
                      OR (sender_id = '$recipient_id' AND recipient_id = '$logged_in_user_id') ORDER BY timestamp ASC";
    $message_result = mysqli_query($conn, $message_query);

    // Handle new message submission
    if (isset($_POST['send_message'])) {
        $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';
        $image_path = '';
    
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $target_dir = "uploads/";  // Create this folder if it doesn't exist
            $image_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
            // Check if the file is an image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
    
        // Insert into the messages table (either text, image, or both)
        if (!empty($message) || !empty($image_path)) {
            $insert_message_query = "INSERT INTO messages (sender_id, recipient_id, message, image) 
                                     VALUES ('$logged_in_user_id', '$recipient_id', '$message', '$image_path')";
            mysqli_query($conn, $insert_message_query);
            
            // Fetch message ID
            $message_id = mysqli_insert_id($conn);
            
            // Send notification
            $notification_message = "You have received a new message from " . htmlspecialchars($sender['name']);
            $insert_notification_query = "INSERT INTO notifications (user_id, sender_id, item_id, notification_type, message, is_read, message_id) 
                                          VALUES ('$recipient_id', '$logged_in_user_id', NULL, 'message', ?, 0, ?)";
            $notif_stmt = $conn->prepare($insert_notification_query);
            $notif_stmt->bind_param("si", $notification_message, $message_id);
            $notif_stmt->execute();
    
            // Refresh the chat
            header("Location: message.php?user_id=$recipient_id");
            exit();
        }
    }
    
}
?>
<?php include('message_navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/message.css">
    <title>Chat</title>
    
</head>

<body>
<main="container">
<div class="chat-container">

    <div class="user-list" id="userList">
        <button class="close-btn" onclick="toggleNavbar()">×</button>
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Search Users...">
            <button onclick="searchUsers()"><i class="fas fa-search"></i></button>
        </div>

        <?php while ($user = mysqli_fetch_assoc($user_result)) { ?>
            <a href="message.php?user_id=<?php echo $user['id']; ?>" onclick="removeImage()" 
            
        >
                <?php echo htmlspecialchars($user['name']); ?>
            </a>
        <?php } ?>
        <!-- Add if needed <div class="back-btn-container">
            <a href="dashboard.php" class="back-btn no-hover"> <img src="css/img/arrow-left.png" alt="Back" width="45" height="45px"/></a>
        </div> -->
    </div>
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <button class="toggle-btn" onclick="toggleNavbar()"><i class="fas fa-search"></i>
    </button>
    <?php if (!isset($recipient)) { ?>
        <div class="chat-placeholder">
        <p>Select a user to start chatting</p>
        </div>

    <?php } ?>

    <!-- Chat Box -->
    <?php if (isset($recipient)) { ?>
        <div class="chat-box">
            <h3 class="fixed-header">
        
                <a href="javascript:history.back();" class="back-button" style="padding-right: 15px;">
                    <i class="fa fa-arrow-left"></i>
                </a>

                <a href="profile.php?user_id=<?php echo $recipient['id']; ?>" class="header-link">
                    <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                        alt="<?php echo htmlspecialchars($recipient['name']); ?>'s Profile Picture" 
                        class="profile-img">
                    <?php echo htmlspecialchars($recipient['name']); ?>
                </a>
            </h3>

<div class="messages">
    <?php while ($message = mysqli_fetch_assoc($message_result)) { ?>
        <div class="message <?php echo $message['sender_id'] == $logged_in_user_id ? 'sent' : 'received'; ?>">
            <?php if (!empty($message['image'])) { ?>
                <img src="<?php echo htmlspecialchars($message['image']); ?>" alt="Sent Image" class="chat-image">
            <?php } ?>
            <p><?php echo htmlspecialchars($message['message']); ?></p>
            <small><?php echo date("F j, Y, g:i a", strtotime($message['timestamp'])); ?></small>
        </div>
    <?php } ?>
</div>

<form action="message.php?user_id=<?php echo $recipient_id; ?>" method="POST" enctype="multipart/form-data" class="message-form">
    <label for="imageUpload" class="image-upload-btn">
        <i class="fas fa-camera"></i> <!-- Camera Icon -->
    </label>
    <input type="file" name="image" id="imageUpload" accept="image/*" onchange="previewImage()">

    <input type="text" name="message" id="messageInput" placeholder="Type your message...">

    <button type="submit" name="send_message"><i class="fas fa-paper-plane"></i></button>
</form>


</div>

    <?php } ?>
</div>
</main>
</body>
<script src="css/js/chat.js"></script>
<script>
  function previewImage() {
    const fileInput = document.getElementById('imageUpload');
    const messageInput = document.getElementById('messageInput');

    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Clear any previous text or image
            messageInput.value = ""; // Clear text if there's an image
            messageInput.style.backgroundImage = `url('${e.target.result}')`;
            messageInput.style.backgroundSize = "contain";
            messageInput.style.backgroundRepeat = "no-repeat";
            messageInput.style.backgroundPosition = "left center"; // Position image on left

            // Adjust the input height based on the image size
            messageInput.style.height = "120px"; // Increase height when an image is selected
        };
        reader.readAsDataURL(fileInput.files[0]);
    } else {
        // Reset to normal state when no image is selected
        messageInput.style.height = "40px"; // Shrink back to default height
        messageInput.style.backgroundImage = "none"; // Remove image preview
    }
}

function toggleNavbar() {
    var userList = document.getElementById("userList");
    userList.classList.toggle("open"); 
}


</script>


</html>
