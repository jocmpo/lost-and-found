<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../auth.php");
    exit();
}

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Construct the query with search and filter options
$query = "SELECT reports.*, users.name AS user_name, users.status AS punishment, items.image AS item_image 
          FROM reports
          LEFT JOIN users ON reports.user_id = users.id
          LEFT JOIN items ON reports.item_id = items.id
          WHERE 1";  // Always true, makes appending conditions easier

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (reports.reason LIKE '%$search%' OR users.name LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $query .= " AND reports.status = '$status_filter'";
}

$query .= " ORDER BY reports.created_at DESC";

$result = mysqli_query($conn, $query);

// Handle report actions (verification or rejection)
if (isset($_POST['action'])) {
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    $punishment = $_POST['punishment'] ?? null;
    $suspension_duration = $_POST['suspension_duration'] ?? null;

    $report_query = "SELECT * FROM reports WHERE id = '$report_id'";
    $report_result = mysqli_query($conn, $report_query);
    $report = mysqli_fetch_assoc($report_result);
    $user_id = $report['user_id'];

    if ($action === 'verify') {
        $new_report_status = 'Verified';

        if ($punishment) {
            if ($punishment === 'Suspended' && $suspension_duration) {
                $new_user_status = 'Suspended';
                $suspension_end = date('Y-m-d H:i:s', strtotime("+$suspension_duration days"));

                $update_user_query = "UPDATE users SET status = 'Suspended', suspended_until = '$suspension_end' WHERE id = '$user_id'";
                mysqli_query($conn, $update_user_query);
            } else {
                $new_user_status = $punishment;
                $update_user_query = "UPDATE users SET status = '$new_user_status' WHERE id = '$user_id'";
                mysqli_query($conn, $update_user_query);
            }
        }
    } elseif ($action === 'reject') {
        $new_report_status = 'Rejected';
        $update_user_query = "UPDATE users SET status = NULL WHERE id = '$user_id'";
        mysqli_query($conn, $update_user_query);
    }

    $update_report_query = "UPDATE reports SET status = '$new_report_status' WHERE id = '$report_id'";
    mysqli_query($conn, $update_report_query);

    header("Location: report_management.php");
    exit();
}

include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        
    <h1>Report Management</h1>

    <form method="get" action="report_management.php" class="item-management-form">
        <input type="text" name="search" placeholder="Search by reason or name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        
        <select name="status">
            <option value="">All Status</option>
            <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Verified" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Verified') ? 'selected' : ''; ?>>Verified</option>
            <option value="Rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
        </select>

        <button type="submit">Search</button>
        <a href="report_management.php" class="reset-btn">Reset</a>
    </form>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Reported By</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Punishment</th>
                <th>Date Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($report = mysqli_fetch_assoc($result)) { ?>
  
        <td><?php echo $report['id']; ?></td>
        <td>
        
            <?php 
                $images = explode(',', $report['item_image']); 
                if (!empty($images[0])): 
            ?>
                <img src="../uploads/<?php echo htmlspecialchars($images[0]); ?>" 
                     onerror="this.onerror=null;this.src='../css/img/no-pictures.png'; this.classList.add('fallback-image');" 
                     class="zoom-image small-image" />
            <?php 
                endif; 
            ?><br>
                        <a href="../item_detail.php?item_id=<?php echo $report['item_id']; ?>" target="_blank" class="view-reported-link">
                            <i class="fas fa-eye"></i> View
                        </a>

        </td>
                    
                    <td><?php echo $report['user_name']; ?></td> <!-- Display User Name -->
                    <td><?php echo $report['reason']; ?></td>
                    <td><?php echo $report['status']; ?></td>
                    <td><?php echo $report['punishment'] ? $report['punishment'] : 'None'; ?></td> <!-- Display Punishment -->
                    <td>
                        <?php echo date("F d, Y h:i A", strtotime($report['created_at'])); ?> <!-- Date Formatted -->
                    </td>
                    <td>
                        <form action="report_management.php" method="POST" style="display:inline;">
                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                            <select name="punishment" onchange="toggleDurationOptions(this)">
                                <option value="">Select Punishment (if applicable)</option>
                                <option value="Under Investigation">Under Investigation</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Banned">Banned</option>
                            </select>
                            
                            <div class="duration-container" id="duration-container">
                                <label for="suspension_duration">Suspension Duration (in days):</label>
                                <input type="number" name="suspension_duration" id="suspension_duration" min="1" placeholder="Enter duration in days">
                                <br>
                                <label for="immediate_lift">Or lift immediately:</label>
                                <input type="checkbox" name="immediate_lift" id="immediate_lift" value="true">
                            </div>

                            <button type="submit" name="action" value="verify" class="verify-btn">
                                <i class="fas fa-check"></i> Verify
                            </button>
                            <button type="submit" name="action" value="reject" class="reject-btn">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        function toggleDurationOptions(select) {
            var durationContainer = document.getElementById('duration-container');
            if (select.value === 'Suspended') {
                durationContainer.classList.add('active');
            } else {
                durationContainer.classList.remove('active');
            }
        }
    </script>
</body>
</html>
