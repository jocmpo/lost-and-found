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

// Fetch categories for the dropdown (Lost/Found)
$type_query = "SELECT DISTINCT type FROM items";
$type_result = mysqli_query($conn, $type_query);

// Handle search
$search_query = "SELECT * FROM items WHERE 1=1 AND type != 'Claimed'";  // Exclude Claimed items by default

// Apply filters based on user input
if (isset($_GET['search'])) {
    $title = $_GET['title'];
    $type = $_GET['type'];
    $location = $_GET['location'];

    if (!empty($title)) {
        $search_query .= " AND title LIKE '%" . mysqli_real_escape_string($conn, $title) . "%'";
    }

    if (!empty($type)) {
        $search_query .= " AND type = '" . mysqli_real_escape_string($conn, $type) . "'";
    }

    if (!empty($location)) {
        $search_query .= " AND location LIKE '%" . mysqli_real_escape_string($conn, $location) . "%'";
    }
}


$items_result = mysqli_query($conn, $search_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/no_message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include('web_navbar.php'); ?>

<form action="search.php" method="GET">
    <div class="form-row">
        <label for="title">Keyword:</label>
        <input type="text" id="title" name="title" value="<?php echo isset($_GET['title']) ? htmlspecialchars($_GET['title']) : ''; ?>">

        <label for="type">Category:</label>
        <select id="type" name="type">
            <option value="">All</option>
            <?php while ($row = mysqli_fetch_assoc($type_result)) { 
                if ($row['type'] != 'Claimed') { ?>
                    <option value="<?php echo $row['type']; ?>" <?php echo isset($_GET['type']) && $_GET['type'] == $row['type'] ? 'selected' : ''; ?>>
                        <?php echo $row['type']; ?>
                    </option>
            <?php } } ?>
        </select>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">

        <button type="submit" name="search">Search</button>
    </div>
</form>

<div class="items">
    <?php if (mysqli_num_rows($items_result) > 0) { ?>
        <ul class="items-list">
            <?php while ($item = mysqli_fetch_assoc($items_result)) { 
                // Fetch the user who posted the item
                $poster_id = $item['user_id'];
                $user_query = "SELECT * FROM users WHERE id = '$poster_id'";
                $user_result = mysqli_query($conn, $user_query);
                $user = mysqli_fetch_assoc($user_result);

               // Fetch profile photo or use default
                $profile_photo = isset($user['photo']) && !empty($user['photo']) ? $user['photo'] : 'css/img/user.png';

                date_default_timezone_set('Asia/Manila');
                
                $created_at = $item['created_at']; 
                $posted_time = new DateTime($created_at); 
                $posted_time->setTimezone(new DateTimeZone('Asia/Manila')); 
                
                $current_time = new DateTime(); // Current time
                $current_time->setTimezone(new DateTimeZone('Asia/Manila'));
                
                $time_diff = $current_time->diff($posted_time);
                
                if ($time_diff->y > 0) {
                    $time_ago = ($time_diff->y == 1) ? '1 year ago' : $time_diff->y . ' years ago';
                } elseif ($time_diff->m > 0) {
                    $time_ago = ($time_diff->m == 1) ? '1 month ago' : $time_diff->m . ' months ago';
                } elseif ($time_diff->d > 0) {
                    $time_ago = ($time_diff->d == 1) ? '1 day ago' : $time_diff->d . ' days ago';
                } elseif ($time_diff->h > 0) {
                    $time_ago = ($time_diff->h == 1) ? '1 hour ago' : $time_diff->h . ' hours ago';
                } elseif ($time_diff->i > 0) {
                    $time_ago = ($time_diff->i == 1) ? '1 minute ago' : $time_diff->i . ' minutes ago';
                } elseif ($time_diff->s >= 0) {
                    $time_ago = ($time_diff->s == 0) ? 'Just now' : (($time_diff->s == 1) ? '1 second ago' : $time_diff->s . ' seconds ago');
                } else {
                    $time_ago = 'Just now'; 
                }
                ?>
               
               <li id="item-<?php echo $item['id']; ?>" class="item-card">

                <p>
                    <span class="profile-container">
             
                <button class="report-btn" onclick="openReportModal(<?php echo $item['id']; ?>)">
                    <i class="fa fa-ellipsis-h"></i>
                </button>

                    <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>'s Profile Picture" class="profile-img" >
                    <a href="profile.php?user_id=<?php echo $user['id']; ?>" class="profile-link">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                    </span>
                </p>
                <hr class="profile-divider">
                
                <p>
                <a class="zoom-link">
                    <?php 
                        $images = explode(',', $item['image']); 
                        if (!empty($images[0])): 
                    ?>
                        <img src="uploads/<?php echo htmlspecialchars($images[0]); ?>" 
                            onerror="this.onerror=null;this.src='css/img/no-pictures.png'; this.classList.add('fallback-image');" 
                            class="zoom-image" />
                    <?php 
                        endif; 
                    ?>
                </a>

                </p>

<div class="status-container">
                    <h4 class="status <?php echo htmlspecialchars($item['type']); ?>">
                        <?php echo htmlspecialchars($item['title']); ?> 
                    </h4>
                    <h4 class="status <?php echo strtolower(htmlspecialchars($item['type'])); ?>">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($item['type']); ?>
                    </h4> 
                </div>

                <p class="item-description"><strong><i class="fas fa-info-circle"></i></strong> <?php echo htmlspecialchars($item['description']); ?></p>
                <p class="item-location">
                        <strong><i class="fas fa-location-dot"></i></strong> 
                        <a href="https://www.google.com/maps?q=<?php echo urlencode(htmlspecialchars($item['location'])); ?>" 
                        target="_blank">
                            <?php echo htmlspecialchars($item['location']); ?>
                        </a>
                    </p>
                    <p><strong><i class="fas fa-calendar"></i></strong> <?php echo date("F j, Y h:i A", strtotime($item['created_at'])); ?></p>
                    <p style="text-align: right;"><strong></strong> <?php echo $time_ago; ?></p>
                    <!-- Private Message Link -->
                    <p><a href="message.php?user_id=<?php echo $user['id']; ?>&message=Hi" class="chat-link"><i class="fas fa-comment"></i> Chat</a></p>
                    <a href="item_detail.php?item_id=<?php echo $item['id']; ?>" class="item-link">
                        View Details
                    </a>

                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="no-items-message">No items found matching your criteria.</p>
    <?php } ?>

    <!-- Modal for Image Zoom -->
    <div id="image-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img id="modal-image" src="" alt="Zoomed Image">
            </div>
        </div>
    </div>

    <!-- Modal for Reporting Item -->
    <div id="report-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Report</h3>
        <hr class="profile-divider">
        <form action="report_item.php" method="POST">
            <input type="hidden" id="report-item-id" name="item_id" value="">
            <label for="reason">Reason for Reporting:</label>
            <select id="reason" name="reason" onchange="toggleOtherReason()" required>
                <option value="Inappropriate Content">Inappropriate Content</option>
                <option value="Spam">Spam</option>
                <option value="Misleading Information">Misleading Information</option>
                <option value="Other">Other</option>
            </select>

            <div id="other-reason-container" style="display:none;">
                <label for="other-reason">Please specify:</label>
                <input type="text" id="other-reason" name="other_reason" placeholder="Describe the issue" />
            </div>

            <button type="submit" name="report-submit">Submit Report</button>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Open modal with image
        $('.zoom-link').on('click', function() {
            var imgSrc = $(this).find('img').attr('src');
            $('#modal-image').attr('src', imgSrc);
            $('#image-modal').show();
        });

        // Close the modal
        $('.close-btn').on('click', function() {
            $('#image-modal').hide();
        });

        // Close the modal if clicked outside the image
        $('#image-modal').on('click', function(e) {
            if ($(e.target).is('#image-modal')) {
                $('#image-modal').hide();
            }
        });
    });

function openReportModal(itemId) {
document.getElementById('report-item-id').value = itemId;
document.getElementById('report-modal').style.display = 'block';
}

// Close the modal when the user clicks the close button
document.getElementById('report-modal').querySelector('.close-btn').onclick = function() {
    document.getElementById('report-modal').style.display = 'none';
};

// Close the modal when the user clicks outside of the modal content
window.onclick = function(event) {
    if (event.target === document.getElementById('report-modal')) {
        document.getElementById('report-modal').style.display = 'none';
    }
};

function toggleOtherReason() {
    var reason = document.getElementById('reason').value;
    var otherReasonContainer = document.getElementById('other-reason-container');
    
    if (reason === 'Other') {
        otherReasonContainer.style.display = 'block';
    } else {
        otherReasonContainer.style.display = 'none';
    }
};
// Add event listener to the image for toggling zoom
document.querySelector('.modal-content img').onclick = function () {
    if (this.classList.contains('zoomed')) {
        this.classList.remove('zoomed'); // Remove zoom
    } else {
        this.classList.add('zoomed'); // Add zoom
    }
};

</script>
</body>
</html>
<?php
include 'includes/footer.php';
?>

<?php
mysqli_close($conn);
?>
