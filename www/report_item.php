<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include('includes/db.php');

if (isset($_POST['report-submit'])) {
    $item_id = $_POST['item_id'];
    $reason = $_POST['reason'];

    // If the reason is "Other", use the other_reason input
    if ($reason == 'Other') {
        $reason = $_POST['other_reason'];
    }

    $user_id = $_SESSION['user_id'];

    // Insert the report into the database
    $report_query = "INSERT INTO reports (item_id, user_id, reason) VALUES ('$item_id', '$user_id', '$reason')";
    if (mysqli_query($conn, $report_query)) {
        echo "<script>alert('Your report has been submitted successfully.'); window.history.back();</script>";
    } else {
        echo "<script>alert('Error submitting report. Please try again later.'); window.history.back();</script>";
    }
}

mysqli_close($conn);
?>
