<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$user_id = $_SESSION['user_id'];
$photo = 'css/img/user.png';

// Fetch the current profile picture and name from the database
$query = "SELECT photo, name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($db_picture, $name);
if ($stmt->fetch() && $db_picture) {
    $photo = $db_picture;
}
$stmt->close();

?>
<nav class="navbar">
<!-- Mobile Navbar -->
<div class="mobile-navbar">
<a href="#" class="menu-icon" onclick="openNav()">
    <i class="fas fa-bars"></i></a>

</div>

<!-- Side Navbar -->
<div class="sidenav" id="mobileSidenav">
<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>

    <a href="profile.php">
        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Profile Picture" class="sidenav-profile-photo">
    </a>

    <div class="sidenav-links">
        <a href="profile.php">Profile</a>
        <a href="faq.php">FAQ</a>
        <a href="about.php">About</a>
        <a href="terms.php">Terms and Conditions</a>
    </div>

    <div class="sidenav-logout">
        <a href="logout.php"><i class="fa fa-arrow-circle-left"></i> Logout</a>
    </div>
</div>

    <!-- Desktop Navbar -->
    <div class="navbar-right">
        <ul class="navbar-links">
            <li><a href="dashboard.php" class="navbar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" id="home"><i class="fa fa-home"></i></a></li>
            <li><a href="post_item.php" class="navbar-link <?php echo basename($_SERVER['PHP_SELF']) == 'post_item.php' ? 'active' : ''; ?>" id="post"><i class="fa fa-pencil-square"></i></a></li>
            <li><a href="search.php" class="navbar-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" id="search"><i class="fa fa-search"></i></a></li>
            <li><a href="notification.php" class="navbar-link <?php echo basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : ''; ?>" id="notif"><i class="fa fa-bell"></i></a></li>
            <li><a href="message.php" class="navbar-link <?php echo basename($_SERVER['PHP_SELF']) == 'message.php' ? 'active' : ''; ?>" id="chat"><i class="fa fa-comment"></i></a></li>

            <li class="dropdown">
                <a href="#" class="dropbtn" onclick="toggleDropdown(event)">
                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Profile Picture" class="profile-photo">
                </a>
                <div class="dropdown-content" id="profileDropdown"> 
                    <a href="profile.php">View Profile</a>
                    <a href="logout.php"><i class="fa fa-arrow-circle-left"></i> Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>


<link rel="stylesheet" href="css/web_navbar.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

<script>
    function toggleDropdown(event) {
    event.preventDefault(); 
    let dropdown = document.getElementById("profileDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";

    document.addEventListener("click", function (e) {
        if (!event.target.closest(".dropdown")) {
            dropdown.style.display = "none";
        }
    }, { once: true });
}
function openNav() {
    document.getElementById("mobileSidenav").style.left = "0";
    document.body.classList.add("sidenav-open"); 
    document.addEventListener("click", outsideClickListener);
}

function closeNav() {
    document.getElementById("mobileSidenav").style.left = "-250px";
    document.body.classList.remove("sidenav-open"); 

    document.removeEventListener("click", outsideClickListener);
}

function outsideClickListener(event) {
    const sidenav = document.getElementById("mobileSidenav");
    const menuIcon = document.querySelector(".menu-icon"); 
    if (!sidenav.contains(event.target) && !menuIcon.contains(event.target)) {
        closeNav();
    }
}

</script>