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
    <title>Terms & Privacy Policy</title>
    <link rel="stylesheet" href="css/terms.css">
</head>
<body>
<div class="container">
        <h1>Terms and Privacy Policy</h1>
        <section>
            <h3>1. Terms of Service</h3>
            <p>By using our Lost and Found website, you agree to the following terms:</p>
            <ul>
                <li><p><strong>Account Responsibility:</strong> You are responsible for maintaining the confidentiality of
                    your account information and for all activities that occur under your account. Notify us immediately
                    of any unauthorized access.</p></p></li>
                <li><p><strong>Use of Services:</strong> You agree to use our website only for lawful purposes and in
                    accordance with our terms. You may not use our services to post illegal, offensive, harmful, or
                    misleading content.</p></p></li>
                <li><p><strong>Content Ownership:</strong> All content posted on our website, including but not limited to
                    images, text, and videos, is the property of the Lost and Found platform or its respective owners.
                    You must not infringe on copyrights or other intellectual property rights.</p></p></li>
                <li><p><strong>Termination of Account:</strong> We reserve the right to suspend or terminate your account
                    if you violate any of the terms outlined in this policy. This includes, but is not limited to,
                    fraudulent activities or posting false claims.</p></p></li>
                <li><p><strong>Disclaimer of Liability:</strong> We are not responsible for any damages, losses, or
                    disputes resulting from your use of the website or any third-party services linked to it. Use of our
                    website is at your own risk.</p></p></li>
                <li><p><strong>Third-Party Links:</strong> Our website may contain links to third-party websites. We do not
                    endorse or take responsibility for the content, privacy policies, or practices of these websites.</p>
                </p></li>
                <li><p><strong>Prohibited Activities:</strong> You agree not to engage in activities that could harm the
                    website, its users, or the platform, such as hacking, spreading malware, or using automated systems
                    to extract data without permission.</p></p></li>
            </ul>
        </section>

        <section>
            <h3>2. Privacy Policy</h3>
            <p>We take your privacy seriously. This Privacy Policy explains how we collect, use, and protect your
                personal information:</p>
            <ul>
                <li><p><strong>Information Collection:</strong> We collect personal information that you provide to us,
                    such as your name, email address, and details about lost or found items. We may also collect
                    non-personal data, such as browser type and IP address, for analytics.</p></p></li>
                <li><p><strong>Use of Information:</strong> The information we collect is used to provide you with the best
                    possible service, facilitate item recovery, communicate with you, and improve the functionality of
                    our website.</p></li>
                <li><p><strong>Cookies:</strong> We may use cookies to enhance your user experience and track website usage
                    for analytics purposes. You can control the use of cookies through your browser settings. Disabling
                    cookies may limit certain features.</p></li>
                <li><p><strong>Data Sharing:</strong> We do not share your personal information with third parties unless
                    required by law, necessary for operational purposes (e.g., payment processing), or with your
                    explicit consent.</p></li>
                <li><p><strong>Data Security:</strong> We employ industry-standard security measures to protect your
                    personal information from unauthorized access, alteration, or disclosure. However, no system is
                    entirely secure, and we cannot guarantee absolute protection.</p></li>
                <li><p><strong>Your Rights:</strong> You have the right to access, correct, or delete your personal data at
                    any time by contacting us directly. You may also request a copy of the data we hold about you.</p></p></p></li>
                <li><p><strong>Data Retention:</strong> We retain personal data only for as long as necessary to fulfill
                    the purposes outlined in this policy or as required by law.</p></p></p></li>
            </ul>
        </section>

        <section>
            <h3>3. Changes to the Terms and Privacy Policy</h3>
            <p>We may update these terms and privacy policy periodically. Any changes will be posted on this page with
                the updated date. Please review this page regularly to stay informed about our terms and privacy
                practices. Continued use of our services after changes indicates your acceptance of the updated terms.
            </p>
        </section>

        <section>
            <h3>4. User Responsibilities</h3>
            <ul>
                <li><p>Ensure all information provided on the platform is accurate and truthful.</p></li>
                <li><p>Report suspicious or fraudulent activity promptly.</p></li>
                <li><p>Comply with all applicable laws while using the website.</p></li>
                <li><p>Refrain from using the platform to harass, intimidate, or harm others.</p></li>
            </ul>
        </section>

        <section>
            <h3>5. Contact Information</h3>
            <p>If you have any questions, concerns, or feedback about our Terms & Privacy Policy, please contact us
                through the following channels:</p>
            <ul>
                <li><p>Email: <a href="mailto:support@lostandfound.com">support@lostandfound.com</a></p></li>
                <li><p>Phone: +1 (234) 567-8901</p></li>
                <li>
                    <p>Address: <a href="https://www.google.com/maps?q=Anonas+Street+1016+628+Sta+Mesa+Metro+Manila" target="_blank">Anonas Street 1016 628 Sta Mesa Metro Manila</a></p>
                </li>

            </ul>
        </section>

        <section>
            <h3>6. Governing Law</h3>
            <p>These terms and conditions are governed by and construed in accordance with the laws of the Philippines. Any disputes arising out of or related to these terms shall be subject to the exclusive
                jurisdiction of the courts in the Philippines.</p>
        </section>
    </div>

</body>

<?php
include 'includes/footer.php';
?>

</html>
