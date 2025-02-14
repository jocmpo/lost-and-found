<?php
// Enable CORS for API requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Start session at the beginning
session_start();
include('includes/db.php');

// Ensure database connection is established
if (!isset($conn)) {
    die("Database connection not established.");
}

$message_sent = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'); 
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $envPath = __DIR__ . '/.env'; // Ensure correct path

if (!file_exists($envPath)) {
    die("Error: .env file not found.");
}

// Read and parse the .env file
$envContents = file_get_contents($envPath);
preg_match('/SENDGRID_API_KEY=(.+)/', $envContents, $matches);

if (!isset($matches[1])) {
    die("Error: API Key not found in .env file.");
}

$apiKey = trim($matches[1]); // Extract API key

if (empty($apiKey)) {
    die("Error: API Key is empty. Check your .env file.");
}


    $recipient = "annocmpo@gmail.com";
    $verifiedSender = "annocmpo@gmail.com";  
    $emailData = [
        "personalizations" => [[
            "to" => [["email" => $recipient]],
            "subject" => "Inquiry from " . $name
        ]],
        "from" => ["email" => $verifiedSender],
        "content" => [[
            "type" => "text/plain",
            "value" => "Name: $name\nEmail: $email\n\nMessage:\n" . $message
        ]]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $message_sent = ($httpCode == 202) ? "Email sent successfully!" : "Failed to send email. Error Code: " . $httpCode;
}

// Include navbar based on session
if (!isset($_SESSION['user'])) {
    include('navbar.php');
} else {
    include('web_navbar.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="css/inquiry.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="box">
        <div class="logo-container">
            <img src="css/img/mail_1.gif" alt="Mail Icon" width="150" height="150" />
            <h3>We'd Love to Hear From You</h3>
        </div>

        <div class="contact-info">
            <p><strong>Address: </strong><a href="https://www.google.com/maps?q=Anonas+Street+1016+628+Sta+Mesa+Metro+Manila" target="_blank">Anonas Street 1016 628 Sta Mesa Metro Manila</a></p>
            <p><strong>Email:</strong> <a href="mailto:support@lostandfound.com">support@lostandfound.com</a></p>
            <p><strong>Phone:</strong> +1 (234) 567-8901</p>
        </div>

        <form id="contact-form" method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="message">Message:</label>
            <textarea name="message" rows="5" placeholder="Write your message here..." required></textarea><br>
            
            <button type="submit">Send Message</button>
        </form>
        
        <?php if (!empty($message_sent)): ?>
            <div class='message-status'><?php echo $message_sent; ?></div>
        <?php endif; ?>
    </div>

    <div class="map">
        <h3>Our Location</h3>
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6092.353951690452!2d120.99820237316161!3d14.574407162129646!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c9ed93f4c213%3A0x256db62ecb27be09!2sPolytechnic%20University%20of%20the%20Philippines%20-%20Sta.%20Mesa%2C%20Manila!5e0!3m2!1sen!2sph!4v1673964448707!5m2!1sen!2sph" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>

    <script>
        window.onload = function() {
            var message = document.querySelector('.message-status');
            if (message) {
                setTimeout(function() {
                    message.style.display = 'none';
                }, 3000);
            }
        }
    </script>
</body>
<?php include 'includes/footer.php'; ?>
</html>
