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
        <h1><img src="css/img/information.png" width="35" height="35" class="icon" alt="">About</h1>
        
        <section>
            <h3><img src="css/img/mission.gif" width="40" height="40" alt="Description of the image">
            Our Mission</h3>
            <p>At Lost and Found, our mission is to help individuals reunite with their lost belongings by providing an easy-to-use platform for reporting, searching, and finding lost items. Whether it's a misplaced wallet, lost phone, or missing keys, our goal is to connect people with their items in a timely and efficient manner.</p>
        </section>

        <section>
            <h3><img src="css/img/management.gif" width="35" height="35" alt="Description of the image">
            How It Works</h3>
            <p>The process is simple and effective. Here’s how our Lost and Found platform works:</p>
            <ul>
                <li><strong>Report Lost Items:</strong> If you've lost something, you can create a report on our website. Provide a detailed description and any relevant images to help others identify your lost item.</li>
                <li><strong>Browse Found Items:</strong> If you find something that doesn't belong to you, you can post it on our website. Be sure to include details like location and any distinguishing features to help the owner recognize their lost item.</li>
                <li><strong>Reunite:</strong> If your lost item is found, you'll be notified so you can retrieve it. Our platform helps you connect with the person who found your item in a safe and secure way.</li>
            </ul>
        </section>

        <section>
            <h3><img src="css/img/work-experience.gif" width="35" height="35" alt="Description of the image">
            Why Choose Us?</h3>
            <p>We understand the frustration and inconvenience that comes with losing personal belongings. Our platform is designed with ease of use in mind, allowing you to quickly and effectively report and find lost items. Here’s why you should choose Lost and Found:</p>
            <ul>
                <li><strong>User-Friendly Interface:</strong> Our website is designed to be intuitive and easy to navigate for users of all ages and technical backgrounds.</li>
                <li><strong>Secure and Confidential:</strong> Your information is kept private and secure, ensuring a safe process for both reporting and claiming lost items.</li>
                <li><strong>Global Reach:</strong> Whether you’ve lost an item in your local area or while traveling, our platform can help you find it no matter where it was lost.</li>
                <li><strong>Community-Driven:</strong> Lost and Found is built on a community-based model where individuals help each other reconnect with lost items, making it easier to find and return belongings.</li>
            </ul>
        </section>

        <section>
            <h3><img src="css/img/opportunity.png" width="25" height="25" alt="Description of the image">
            Our Vision</h3>
            <p>We aim to be the leading platform for connecting people with their lost belongings, making it easier for individuals to report, recover, and return lost items across the globe. Through continuous innovation and user feedback, we strive to improve our platform and help people regain their possessions with minimal hassle.</p>
        </section>

        <section>
            <h3><img src="css/img/mail.gif" width="35" height="35" alt="Description of the image">
            Contact Us</h3>
            <p>If you have any questions, feedback, or suggestions, feel free to contact us:</p>
            <ul>
                <li>Email: <a href="mailto:support@lostandfound.com">support@lostandfound.com</a></li>
                <li>Phone: +1 (234) 567-8901</li>
            </ul>
        </section>
    </div>
</body>
</html>
<?php
include 'includes/footer.php';
?>