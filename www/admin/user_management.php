<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../auth.php");
    exit();
}

// Include database connection
include('../includes/db.php');

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $createdAt = $_POST['created_at']; // Added date input

    $query = "UPDATE users SET name = ?, email = ?, role = ?, created_at = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssii', $name, $email, $role, $createdAt, $userId);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: user_management.php?message=UserUpdated");
        exit();
    } else {
        echo "Error: Could not update user.";
    }
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $userId = $_GET['delete_id'];

    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: user_management.php?message=UserDeleted");
        exit();
    } else {
        echo "Error: Could not delete user.";
    }
}

// Handle search and filter
$search = '';
$role_filter = '';

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (isset($_GET['role'])) {
    $role_filter = mysqli_real_escape_string($conn, $_GET['role']);
}

// Base query
$query = "SELECT id, name, email, role, created_at FROM users WHERE 1";

if ($search != '') {
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}

if ($role_filter != '') {
    $query .= " AND role = '$role_filter'";
}

// Execute query
$result = mysqli_query($conn, $query);

// Fetch results
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script>
        // Open the modal for editing
        function openModal(userId, userName, userEmail, userRole, userDate) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('userId').value = userId;
            document.getElementById('userName').value = userName;
            document.getElementById('userEmail').value = userEmail;
            document.getElementById('userRole').value = userRole;
            document.getElementById('userDate').value = userDate;
        }

        // Close the modal
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Confirm deletion
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'user_management.php?delete_id=' + userId;
            }
        }
    </script>
</head>

<body>

    <!-- Main Content -->
    <div class="dashboard-container">
        <h1>User Management</h1>

        <!-- Search and Filter -->
        <form method="get" action="user_management.php" class="user-management-form">
            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
            <select name="role">
                <option value="">All Roles</option>
                <option value="admin" <?php echo ($role_filter == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo ($role_filter == 'user') ? 'selected' : ''; ?>>User</option>
            </select>
            <button type="submit">Search</button>
            <a href="user_management.php" class="reset-btn">Reset</a>
        </form>

        <!-- User Table -->
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo date("F d, Y h:i A", strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="openModal(
                            <?php echo $user['id']; ?>,
                            '<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>',
                            '<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>',
                            '<?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>'
                        )">
                            <img src="../css/img/edit.png" alt="Edit" width="20" height="20">
                        </a>

                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                            <img src="../css/img/trash.png" alt="Delete" width="20" height="20">
                        </a>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal for Editing User -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit User</h2>
            <form action="user_management.php" method="POST">
                <input type="hidden" id="userId" name="user_id">
                <label for="userName">Name:</label>
                <input type="text" id="userName" name="name" required>
                <br><br>
                <label for="userEmail">Email:</label>
                <input type="email" id="userEmail" name="email" required>
                <br><br>
                <label for="userRole">Role:</label>
                <select id="userRole" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <br><br>
                <input type="submit" value="Update User">
            </form>
        </div>
    </div>

</body>
</html>
