<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

include('includes/db.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$photo = 'css/img/user.png';
$address = '';
$email = '';

// Fetch the current profile picture, name, email, and address from the database
$query = "SELECT photo, name, email, address FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($db_picture, $name, $db_email, $db_address);
if ($stmt->fetch()) {
    $photo = $db_picture;
    $address = $db_address;
    $email = $db_email; // Ensure email is assigned correctly
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) { 
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo']['type'];

        // Check if file is of a valid type
        if (in_array($file_type, $allowed_types)) {
            // Save the uploaded image to a directory
            $target_dir = "uploads/profile/";
            $target_file = $target_dir . basename($_FILES["photo"]["name"]);

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                // Update the profile picture in the database
                $query = "UPDATE users SET photo = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $target_file, $user_id);
                $stmt->execute();
                $_SESSION['message'] = 'Profile picture updated successfully.';
                $_SESSION['message_type'] = 'success'; 
            } else {
                $_SESSION['message'] = 'Error uploading photo.';
                $_SESSION['message_type'] = 'error'; 
            }
        } else {
            $_SESSION['message'] = 'Invalid file type. Only images are allowed.';
            $_SESSION['message_type'] = 'error'; 
        }
    }
}


// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get the values from the POST request
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Fetch the current address, latitude, and longitude from the database
    $stmt = $conn->prepare("SELECT address, latitude, longitude FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($current_address, $current_latitude, $current_longitude);
    $stmt->fetch();
    $stmt->close();

    // Check if the address is different from the current one
    if ($address !== $current_address) {
        // Prepare the SQL query to update the address
        $stmt = $conn->prepare("UPDATE users SET address = ?, latitude = ?, longitude = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $address, $latitude, $longitude, $user_id);
        
        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Address updated successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error updating address: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
        
        $stmt->close();
    }
}

// Update name
if (!empty($_POST['name'])) {
    $name = $_POST['name'];

    // Fetch current name from the database
    $query = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_name);
    $stmt->fetch();
    $stmt->close();

    // Only update if the name has changed
    if ($name !== $current_name) {
        $query = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = 'Name updated successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error updating name: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
        $stmt->close();
    }
}

// Update email
if (!empty($_POST['email'])) {
    $email = $_POST['email'];

    // Fetch current email from the database
    $query = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_email);
    $stmt->fetch();
    $stmt->close();

    // Only update if the email has changed
    if ($email !== $current_email) {
        // Check if the new email already exists
        $check_email_query = "SELECT id FROM users WHERE email = ?";
        $check_email_stmt = $conn->prepare($check_email_query);
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();
        
        if ($check_email_stmt->num_rows > 0) {
            // Email already exists, set error message
            $_SESSION['message'] = 'This email is already in use. Please choose a different email.';
            $_SESSION['message_type'] = 'error';
        } else {
            // Proceed with updating the email
            $query = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = 'Email updated successfully!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating email: ' . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
            $stmt->close();
        }
        $check_email_stmt->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $user_id = $_SESSION['user_id']; 
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Password validation (at least 6 characters)
    if (strlen($new_password) < 6) {
        $_SESSION['message'] = 'Password must be at least 6 characters.';
        $_SESSION['message_type'] = 'error';
        header("Location: profile_settings.php"); // Redirect to the same page
        exit();
    } else {
        // Fetch the current password hash from the database
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify old password
        if (password_verify($old_password, $hashed_password)) {
            // Hash the new password
            $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_password_hashed, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['message'] = 'Password updated successfully.';
                $_SESSION['message_type'] = 'success';
                header("Location: profile_settings.php"); // Redirect to the same page
                exit();
            } else {
                $_SESSION['message'] = 'Error updating password: ' . $update_stmt->error;
                $_SESSION['message_type'] = 'error';
                header("Location: profile_settings.php"); // Redirect to the same page
                exit();
            }
            $update_stmt->close();
        } else {
            $_SESSION['message'] = 'Old password is incorrect.';
            $_SESSION['message_type'] = 'error';
            header("Location: profile_settings.php"); // Redirect to the same page
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="css/profile_settings.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<?php include('web_navbar.php'); ?>

<body>
    <!-- Success and Error Modals (same as before) -->

    <h3>Edit Profile</h3>
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Profile Picture" class="pp">
    </div>

    <!-- Form for updating profile picture -->
    <form action="profile_settings.php" method="post" enctype="multipart/form-data">
        <label for="photo">Upload New Profile Picture:</label>
        <input type="file" name="photo" id="photo" required>
        <button type="submit" name="update_photo">Update Photo</button>
    </form>

    <!-- Form for updating profile details -->
    <form action="profile_settings.php" method="post">
    <h4>Edit Profile Information</h4> 
    <hr class="custom-divider">

        <label for="name">Edit Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">

        <label for="address">Address:</label>
        <div class="address-input">
            <input type="text" id="address" name="address" placeholder="Type your address">
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <ul id="suggestions" class="suggestions"></ul>
        </div>

        <label for="email">Edit Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <button type="submit" name="update_profile">Save Changes</button>
        <button type="button" id="cancel" class="cancel-button" onclick="window.location.href='profile.php'">Cancel</button>
    </form>

    <form action="profile_settings.php" method="post">
    <h4>Change Password</h4> 
    <hr class="custom-divider">

        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>

        <button type="submit" name="update_password">Update Password</button>

        <button type="button" id="cancel" class="cancel-button" onclick="window.location.href='profile.php'">Cancel</button>
        <a href="forgot_password.php">Forgot Password?</a>
    </form>

        <!-- Success Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <p id="messageText"></p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <p id="errorText"></p>
        </div>
    </div>

</body>

<script>
    // Function to update the address input fields
    function updateLocationInputs(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        // Reverse geocode using OpenCage API
        const apiKey = '9ac6f13e83b3424fac80a49e713503fd'; 
        fetch(`https://api.opencagedata.com/geocode/v1/json?key=${apiKey}&q=${lat}+${lng}&no_annotations=1`)
            .then(response => response.json())
            .then(data => {
                const addressName = data.results && data.results.length > 0 ? data.results[0].formatted : 'Unknown address';
                document.getElementById('address').value = addressName;
            })
            .catch(error => console.log('Error reverse geocoding:', error));
    }

    // Use Geoaddress API to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                // Update input fields with current location
                updateLocationInputs(lat, lng);
            },
            function (error) {
                console.log('Geolocation error:', error);
                alert('Unable to fetch your location. Please select a location manually.');
            }
        );
    } else {
        alert('Geolocation is not supported by your browser. Please select a location manually.');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const addressInput = document.getElementById("address");
        const suggestionsList = document.getElementById("suggestions");
        const latitudeInput = document.getElementById("latitude");
        const longitudeInput = document.getElementById("longitude");

        const API_KEY = "9ac6f13e83b3424fac80a49e713503fd";

        addressInput.addEventListener("input", function () {
            let query = addressInput.value.trim();

            if (query.length > 2) { // Start searching after 3 characters
                fetch(`https://api.opencagedata.com/geocode/v1/json?key=${API_KEY}&q=${query}&no_annotations=1&countrycode=PH`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsList.innerHTML = ""; // Clear previous suggestions

                        if (data.results.length > 0) {
                            data.results.forEach(item => {
                                const li = document.createElement("li");
                                li.textContent = item.formatted;
                                li.dataset.lat = item.geometry.lat;
                                li.dataset.lng = item.geometry.lng;

                                li.addEventListener("click", function () {
                                    addressInput.value = this.textContent;
                                    latitudeInput.value = this.dataset.lat;
                                    longitudeInput.value = this.dataset.lng;
                                    suggestionsList.innerHTML = ""; // Hide suggestions
                                });

                                suggestionsList.appendChild(li);
                            });

                            suggestionsList.style.display = "block"; // Show suggestions
                        }
                    })
                    .catch(error => console.log("Error fetching address suggestions:", error));
            } else {
                suggestionsList.innerHTML = ""; // Hide if input is too short
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener("click", function (e) {
            if (!addressInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = "none";
            }
        });
    });
</script>

<?php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']); 
    unset($_SESSION['message_type']); 
?>
    <script>
        window.onload = function() {
            // Get the modal elements
            var messageModal = document.getElementById('messageModal');
            var errorModal = document.getElementById('errorModal');
            var messageText = document.getElementById('messageText');
            var errorText = document.getElementById('errorText');
            
            // Set message text and modal visibility based on message type
            if ('<?php echo $message_type; ?>' === 'success') {
                messageText.textContent = '<?php echo $message; ?>';
                messageModal.style.display = 'block';
            } else if ('<?php echo $message_type; ?>' === 'error') {
                errorText.textContent = '<?php echo $message; ?>';
                errorModal.style.display = 'block';
            }

            // Close the modal when the user clicks on the close button
            var closeBtns = document.getElementsByClassName('close-btn');
            for (var i = 0; i < closeBtns.length; i++) {
                closeBtns[i].onclick = function() {
                    messageModal.style.display = 'none';
                    errorModal.style.display = 'none';
                };
            }

            // Close the modal if the user clicks outside the modal content
            window.onclick = function(event) {
                if (event.target == messageModal || event.target == errorModal) {
                    messageModal.style.display = 'none';
                    errorModal.style.display = 'none';
                }
            }
        }
    </script>
<?php } ?>


</html>
<?php
include 'includes/footer.php';
?>