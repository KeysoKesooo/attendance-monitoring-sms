<?php
require_once('includes/load.php');  // Assuming your database connection is loaded here

// Check if user is logged in and is a parent (user_level = 3)
if (isset($_SESSION['user_id'])) {
    $parentId = $_SESSION['user_id'];  // Get the logged-in user's ID

    // Query to get the children (students) of the parent using the parent's id
    $sql = "SELECT s.id AS student_id, s.name AS student_name
            FROM student s
            JOIN users u ON s.phone_id = u.id  -- Join the student table with users based on phone_id to user id
            WHERE u.user_level = 3 AND u.id = '{$parentId}'";  // Ensure the user is a parent and matching parentId

    $childrenResult = $db->query($sql);

    // Fetch user details (e.g., name, image)
    $userSql = "SELECT name, image FROM users WHERE id = '{$parentId}'";
    $userResult = $db->query($userSql);
    $user = $userResult->fetch_assoc();  // Assuming the query returns a valid user

} else {
    // Handle if user is not logged in or not a parent
    echo "Please log in as a parent to view children.";
    exit;
}
?>

<div class="mob-nav">

    <a href="dashboard_parent.php" class="nav-item">
        <i class="glyphicon glyphicon-user"></i>
        <span>Dashboard</span>
    </a>

    <!-- Settings Link -->
    <a href="edit_account.php" class="nav-item">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </a>


    <!-- Logout Link -->
    <a href="logout.php" class="nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>