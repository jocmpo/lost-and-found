<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

include('includes/db.php');
if (!isset($_SESSION['user'])) {
    include('navbar.php');
}else{
    include('web_navbar.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="css/terms.css">
</head>
<body>
<div class="container">
<h1><img src="css/img/question.png" width="35" height="35" class="icon" alt="">Frequently Asked Questions (FAQ)</h1>

    <section class="faq-item">
        <h3 class="faq-question">1. What is the Lost and Found website? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">The Lost and Found website is a platform designed to help individuals report and recover lost items or find items they may have lost. Users can post information about lost or found items and connect with others to reunite with their possessions.</p>
    </section>
    <hr>

    <section class="faq-item">
        <h3 class="faq-question">2. How do I create an account? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">To create an account, click on the "Sign Up" button on the homepage and fill out the required information, such as your name, email address, and a password. After signing up, you'll receive a verification email to confirm your account.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">3. How do I report a lost item? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">To report a lost item, log into your account and navigate to the "Report Lost Item" section. Provide details about the item, its description, and where it was lost. You may also upload an image of the item to help others identify it.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">4. How do I claim a found item? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">If you've found an item and wish to claim it, navigate to the "Found Items" section and fill out the form with the item details. Include a brief description and any additional information that might help the owner identify it. You can also upload a picture of the found item.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">5. How can I protect my privacy on the website? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">We take your privacy seriously. We will never share your personal information with third parties unless required by law or with your explicit consent. You can manage your privacy settings in your account preferences, and you have the right to request access, correction, or deletion of your personal data at any time.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">6. What should I do if I forget my password? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">If you've forgotten your password, click on the "Forgot Password" link on the login page. Enter your registered email address, and we'll send you a password reset link to regain access to your account.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">7. Can I report an item without signing up? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">No, you must sign up to report lost or found items on the Lost and Found platform. Signing up allows you to track your reports, receive notifications, and engage with others more easily.</p>
    </section>
    <hr>
    <section class="faq-item">
        <h3 class="faq-question">8. How do I contact customer support? <i class="fa-solid fa-chevron-down arrow"></i></h3>
        <p class="faq-answer">If you have any questions or need assistance, you can contact our customer support team at:
        <br>
            Email: <a href="mailto:support@lostandfound.com">support@lostandfound.com</a>
            <br>Phone: +1 (234) 567-8901
        </p>
    </section>

</div>
</body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const questions = document.querySelectorAll(".faq-question");

    questions.forEach((question) => {
        question.addEventListener("click", function () {
            // Toggle active class for styling
            this.classList.toggle("active");

            // Toggle answer visibility
            const answer = this.nextElementSibling;
            answer.style.display = (answer.style.display === "block") ? "none" : "block";
        });
    });
});

</script>
<?php
include 'includes/footer.php';
?>