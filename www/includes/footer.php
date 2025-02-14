<footer>
    <div class="footer-container">

        <div class="links">
            <ul>
                <li><a href="terms.php">Terms & Privacy Policy</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="inquiry.php">Contact Us</a></li>
            </ul>
        </div>

        <div class="social-media">
            <a href="https://facebook.com"><i class="fab fa-facebook"></i></a>
            <a href="https://twitter.com"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com"><i class="fab fa-instagram"></i></a>
        </div>

        <p>© 2025 Lost and Found. All rights reserved.</p>
    </div>
</footer>
<style>

footer {
    background-color: #f4f4f4; 
    text-align: center;
    color: #657786; 
}

/* Footer Container */
.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Contact Information */
.contact-info p {
    margin: 5px 0;
    font-size: 16px;
}

/* Links Section */
.links ul {
    list-style-type: none;
    padding: 0;
}

.links li {
    display: inline-block;
    margin: 10px;
}

.links a {
    color: #1da1f2; 
    text-decoration: none;
    font-size: 16px;
}

.links a:hover {
    color: #1A4B8A; 
}

/* Social Media Icons */
.social-media a {
    color: #657786; 
    margin: 0 10px;
    font-size: 20px;
    transition: color 0.3s ease;
}

.social-media a:hover {
    color: #1da1f2; 
}

/* Copyright Text */
footer p {
    margin-top: 20px;
    font-size: 14px;
    color: #657786; 
}

/* Mobile Responsiveness - Hide footer on mobile screens */
@media (max-width: 768px) {
    footer {
        display: none; 
    }
}

@media (min-width: 769px) {
    footer {
        display: block; 
    }
}

/* Mobile Responsiveness */

.footer-container {
    padding: 0 20px;
}

.contact-info p,
.links a,
.social-media a {
    font-size: 14px;
}


</style>