<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="sidenav.css" rel="stylesheet">

</head>

<body>
    <div class="navdiv" id="navdiv">
        <ul>
            <li><span onclick="openNav()"><img src="css/img/menu.svg"></span></li>
        </ul>
    </div>

    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a> 
        <a href="#"><img src="css/img/pfpplaceholder.svg" style="height: 100px;"><br>
       <br><br>
        <a href="faq.php">FAQ</a>
        <a href="about.php">About Us</a>
        <a href="inquiry.php">Contact Us</a>
        <a href="terms.php">Terms & Privacy Policy</a>
        <a href="profile.php">Profile</a>
        <a href="profile_settings.php">Account Settings</a>
        <div class="logout">
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

        </div>
    </div>
    <div class="footerdiv" id="footerdiv">
        <ul class="footerlist">
            <li class="footeritem"><a href="search.php"><img src="css/img/search.svg"></a></li>
            <li class="footeritem"><a href="dashboard.php"><img src="css/img/home.svg"></a></li>
            <li class="footeritem"><a href="post_item.php"><i class="fas fa-plus"></i></a></li>
            <li class="footeritem"><a href="notification.php"><img src="css/img/notification.svg"></a></li>
            <li class="footeritem"><a href="chat.php"><img src="css/img/chat.svg"></a></li>
            
        </ul>
    </div>
    <script>
        /* Set the width of the side navigation to 250px and the left margin of the page content to 250px and add a black background color to body */
        function openNav() {
            document.getElementById("mySidenav").style.width = "250px";
            document.body.style.backgroundColor = "rgba(0,0,0,0.3)";
        }

        /* Set the width of the side navigation to 0 and the left margin of the page content to 0, and the background color of body to white */
        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
            document.body.style.backgroundColor = "white";
        }
    </script>
</body>


</html>