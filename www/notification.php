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

$user_id = $_SESSION['user_id'];

// Handle "Mark as Read/Unread" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_read_status'])) {
    $notification_id = intval($_POST['notification_id']);
    $current_status = intval($_POST['current_status']);
    
    // Toggle is_read: If 0 → 1 (Read), If 1 → 0 (Unread)
    $new_status = $current_status == 0 ? 1 : 0;

    $query_update = "UPDATE notifications SET is_read = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    
    if ($stmt_update) {
        $stmt_update->bind_param("ii", $new_status, $notification_id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    // Redirect to prevent form resubmission
    header("Location: notification.php?filter=" . ($_GET['filter'] ?? 'all'));
    exit();
}

// Determine filter: "all" or "unread"
$filter = isset($_GET['filter']) && $_GET['filter'] === 'unread' ? 'unread' : 'all';

// Modify query based on filter selection
$query = "SELECT * FROM notifications WHERE user_id = ?";
if ($filter === 'unread') {
    $query .= " AND is_read = 0";
}
$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die('MySQL prepare statement failed: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the count of unread notifications
$query_unread = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt_unread = $conn->prepare($query_unread);

if ($stmt_unread === false) {
    die('MySQL prepare statement failed: ' . $conn->error);
}

$stmt_unread->bind_param("i", $user_id);
$stmt_unread->execute();
$stmt_unread->bind_result($unread_count);
$stmt_unread->fetch();
$stmt_unread->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="css/notification.css">
    <link rel="stylesheet" href="css/no_message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>

    </style>
</head>
<body>
<?php include('web_navbar.php'); ?>
    <div class="container">
        
        <div class="filter-buttons">
            <a href="notification.php?filter=all" class="<?= $filter == 'all' ? 'active' : '' ?>">All</a>
            <a href="notification.php?filter=unread" class="<?= $filter == 'unread' ? 'active' : '' ?>">Unread (<?= $unread_count ?>)</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <ul class="notification-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="notification-item">
                        <?php if ($row['notification_type'] == 'message'): ?>
                            <p class="notification-header">
                            <a href="message.php?user_id=<?php echo $row['sender_id']; ?>" class="notification-header">
                                    <?php echo htmlspecialchars($row['message']); ?>
                                </a>
                            </p>
                        <?php elseif ($row['notification_type'] == 'post'): ?>
                            <p class="notification-header">
                                <a href="item_detail.php?item_id=<?php echo $row['item_id']; ?>" class="notification-header">
                                    <?php echo htmlspecialchars($row['message']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <p class="notification-date">Posted on: <?php echo $row['created_at']; ?></p>

                        <!-- Display "(New)" if unread -->
                        <?php if ($row['is_read'] == 0): ?>
                            <span class="new-notification">(New)</span>
                        <?php endif; ?>
                        
                        <!-- Toggle Read/Unread -->
                        <form method="POST" action="notification.php?filter=<?= $filter ?>">
                            <input type="hidden" name="notification_id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="current_status" value="<?= $row['is_read']; ?>">
                            <button type="submit" name="toggle_read_status" class="mark-unread-btn">
                                <?= $row['is_read'] == 0 ? 'Mark as Read' : 'Mark as Unread' ?>
                            </button>
                        </form>

                        <br>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="no-items-message">No notifications found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'includes/footer.php'; ?>
