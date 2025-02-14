<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

// Include PHPMailer
require 'assets/PHPMailer/src/Exception.php';
require 'assets/PHPMailer/src/PHPMailer.php';
require 'assets/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('includes/db.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle forgot password process
if (isset($_POST['forgot_password'])) {
    // Get the email from the form
    $email = $_POST['email'];

    // Step 1: Check if the email exists in the database
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If the email exists
    if ($stmt->num_rows > 0) {
        // Generate a verification code
        $verification_code = rand(100000, 999999);
        $expiration_time = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Expire in 15 minutes

        // Get user ID from the database
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Update the database with the verification code and expiration time
        $update_query = "UPDATE users SET password_verification_code = ?, password_verification_expiration = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $verification_code, $expiration_time, $user_id);
        $update_stmt->execute();

        // Step 2: Send the verification email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jnm.cmp@gmail.com';  // Set your SMTP email here
            $mail->Password = 'uqtizyeiyfqyinis'; // Set your SMTP password here
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jnm.cmp@gmail.com', 'Lost and Found');
            $mail->addAddress($email); // Send to the user's email

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body = 'Your password reset verification code is: <strong>' . $verification_code . '</strong>';

            $mail->send();
            $_SESSION['message'] = 'A verification code has been sent to your email. Please check your inbox and enter the code on the next page.';
            header('Location: reset_password.php?email=' . urlencode($email)); // Redirect to reset_password.php
            exit();
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            $_SESSION['error'] = 'Failed to send verification email.';
        }
    } else {
        $_SESSION['error'] = 'No account found with that email address.';
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
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/forgot.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<body>

<!-- Forgot password form -->
<form method="POST" action="">
<img src="css/img/subscription.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
<p class="forgot-password-text">Forgot Password</p>

        <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit" name="forgot_password">Send Verification Code</button>
    
</form>
<!-- Display error or success messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('messageModal')">&times;</span>
            <p><?php echo $_SESSION['message']; ?></p>
        </div>
    </div>
<?php unset($_SESSION['message']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('errorModal')">&times;</span>
            <p><?php echo $_SESSION['error']; ?></p>
        </div>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<script>
// Modal script to close
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Display modals on load
window.onload = function() {
    const messageModal = document.getElementById('messageModal');
    const errorModal = document.getElementById('errorModal');
    if (messageModal) {
        messageModal.style.display = 'block';
    }
    if (errorModal) {
        errorModal.style.display = 'block';
    }
};

</script>

</body>
</html>
