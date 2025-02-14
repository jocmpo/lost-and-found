<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include('includes/db.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle password reset process
if (isset($_POST['reset_password'])) {
    // Get the verification code, email, and new password from the form
    $verification_code = $_POST['verification_code'];
    $new_password = $_POST['new_password'];
    $email = $_GET['email']; // Get email from the URL

    // Step 1: Check if the code exists and is valid
    $query = "SELECT id, password_verification_code, password_verification_expiration FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $stored_code, $stored_expiration);
    $stmt->fetch();

    $current_time = date("Y-m-d H:i:s");

    // Step 2: Check if the verification code matches and is not expired
    if ($verification_code == $stored_code && $current_time <= $stored_expiration) {
        // Step 3: Update the password
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepare and execute the query to update the password
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_password_hashed, $user_id);
        $update_stmt->execute();

        $_SESSION['message'] = 'Password reset successfully!';
        header('Location: auth.php'); // Redirect to login page after successful reset
        exit();
    } else {
        $_SESSION['error'] = 'Invalid or expired verification code.';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<body>

<!-- Reset password form -->
<form method="POST" action="">
<img src="css/img/delivery.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">

    <p class="reset-password-text">Reset Password</p>
    <input type="text" name="verification_code" placeholder="Enter verification code" required>
    <input type="password" name="new_password" placeholder="Enter new password" required>
    <button type="submit" name="reset_password">Reset Password</button>
</form>

<!-- Display error or success messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <p><?php echo $_SESSION['message']; ?></p>
        </div>
    </div>
<?php unset($_SESSION['message']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <p><?php echo $_SESSION['error']; ?></p>
        </div>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<script>
function closeModal() {
    const messageModal = document.getElementById('messageModal');
    const errorModal = document.getElementById('errorModal');

    if (messageModal) {
        messageModal.style.display = 'none';
    }

    if (errorModal) {
        errorModal.style.display = 'none';
    }
}

// Display modals on load
window.onload = function() {
    if (document.getElementById('messageModal')) {
        document.getElementById('messageModal').style.display = 'block';
    }
    if (document.getElementById('errorModal')) {
        document.getElementById('errorModal').style.display = 'block';
    }
};

</script>

</body>

</html>
