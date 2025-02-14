<?php
session_start();
include('../includes/db.php');
// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../auth.php");
    exit();
}

// Check if the report ID is passed
if (isset($_GET['report_id'])) {
    $report_id = $_GET['report_id'];

    // Fetch report data
    $report_query = "SELECT * FROM reports WHERE id = $report_id";
    $report_result = mysqli_query($conn, $report_query);
    $report = mysqli_fetch_assoc($report_result);

    // Handle report verification or rejection
    if (isset($_POST['verify_report'])) {
        $punishment = $_POST['punishment'];
        $status = $_POST['status'];
        $admin_response = $_POST['admin_response'];

        // Update the report with admin response and punishment
        $update_report_query = "UPDATE reports SET status = 'Verified', punishment = '$punishment', admin_response = '$admin_response' WHERE id = $report_id";
        mysqli_query($conn, $update_report_query);

        // Update the user's status based on the punishment
        $user_id = $report['user_id'];
        $update_user_query = "UPDATE users SET status = '$status' WHERE id = $user_id";
        mysqli_query($conn, $update_user_query);

        echo "<script>alert('Report has been verified and punishment applied.'); window.location.href = 'report_management.php';</script>";
    }

    if (isset($_POST['reject_report'])) {
        // Update report status to "Rejected"
        $update_report_query = "UPDATE reports SET status = 'Rejected' WHERE id = $report_id";
        mysqli_query($conn, $update_report_query);

        echo "<script>alert('Report has been rejected.'); window.location.href = 'report_management.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Report</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Verify Report</h2>

    <form action="verify_report.php?report_id=<?php echo $report_id; ?>" method="POST">
        <label for="punishment">Punishment:</label>
        <select name="punishment" required>
            <option value="Suspended">Suspended</option>
            <option value="Banned">Banned</option>
            <option value="Under Investigation">Under Investigation</option>
            <option value="Active">Active</option>
        </select>

        <label for="status">Status Update:</label>
        <select name="status" required>
            <option value="Suspended">Suspended</option>
            <option value="Banned">Banned</option>
            <option value="Under Investigation">Under Investigation</option>
            <option value="Active">Active</option>
        </select>

        <label for="admin_response">Admin Response:</label>
        <textarea name="admin_response" required></textarea>

        <button type="submit" name="verify_report">Verify and Apply Punishment</button>
        <button type="submit" name="reject_report">Reject Report</button>
    </form>

</body>
</html>

<?php
mysqli_close($conn);
?>
