<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

include('includes/db.php');
include('includes/functions.php');

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle the registration process
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user'; 
        // Check if email already exists
        $checkEmailQuery = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $checkEmailQuery);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('Email already exists!'); window.history.back();</script>";
        } else {
            // Insert the new user into the database with the role
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Registration successful!'); window.location.href='auth.php';</script>";
                exit();
                        
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
            }
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
    
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
            exit();
        }
    
        if (strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
            exit();
        }
    
        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
       }
    
    
// Handle the login process
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user'] = $user['name']; // Name for the welcome page
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            
            // Check if the user is an admin
            if ($user['role'] == 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: admin/admin_dashboard.php"); // Redirect to admin dashboard
            } else {
                $_SESSION['is_admin'] = false;
                header("Location: dashboard.php"); // Redirect to regular user dashboard
            }
            exit();
        } else {
            echo "<script> alert ('Invalid credentials!');</script>";
        }
    } else {
        echo "<script> alert('No account found with this email!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="css/auth.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script>
        function toggleForms(form) {
            if(form === 'login') {
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('register-form').style.display = 'none';
            } else {
                document.getElementById('login-form').style.display = 'none';
                document.getElementById('register-form').style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="auth-container">
    <img src="css/img/logo.png" alt="Description of the image" class="logo">
    <div class="auth-buttons">
        
       <!-- <span style="font-size: 40px; margin: 0 10px; font-weight: bold;">|</span> -->
        
    </div>
<!-- Login Form -->
<div id="login-form" class="auth-form" style="display: block;">
    <form action="auth.php" method="POST">
        <label for="login-email">Email</label>
        <div class="input-container">
            <i class="fa fa-envelope"></i>
            <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
        </div>
        
        <label for="login-password">Password</label>
        <div class="input-container">
            <i class="fa fa-lock"></i>
            <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="login">Login</button>
    </form>
    <p>Don't have an account? <a class="reg" onclick="toggleForms('register')"><u>Register</u></a></p>
    <p><a class="reg" href="forgot_password.php"><u>Forgot Password?</u></a></p>
</div>

<!-- Registration Form -->
<div id="register-form" class="auth-form" style="display: none;">
    <form action="auth.php" method="POST">
        <label for="register-name">Full Name</label>
        <div class="input-container">
            <i class="fa fa-user"></i>
            <input type="text" id="register-name" name="name" placeholder="Enter your full name" required>
        </div>

        <label for="register-email">Email</label>
        <div class="input-container">
            <i class="fa fa-envelope"></i>
            <input type="email" id="register-email" name="email" placeholder="Enter your email" required>
        </div>

        <label for="register-password">Password</label>
        <div class="input-container">
            <i class="fa fa-lock"></i>
            <input type="password" id="register-password" name="password" placeholder="Create a password" required>
        </div>

        <label for="register-confirm-password">Confirm Password</label>
        <div class="input-container">
            <i class="fa fa-lock"></i>
            <input type="password" id="register-confirm-password" name="confirm_password" placeholder="Confirm password" required>
        </div>

        <button type="submit" name="register">Register</button>
    </form>
    <p><a class="reg" onclick="toggleForms('login')"><u>Go Back</u></a></p>
</div>

<script>
    function toggleForms(formType) {
        document.getElementById('login-form').style.display = formType === 'login' ? 'block' : 'none';
        document.getElementById('register-form').style.display = formType === 'register' ? 'block' : 'none';
    }
</script>
<script src="assets/js/script.js"></script>
    
</body>
    
</html>
