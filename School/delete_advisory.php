<?php
require_once('includes/load.php');
page_require_level(1); // Ensure the user has the correct permission level

// Check if the 'id' is set in the URL and is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $faculty_category_id = (int)$_GET['id'];  // Get the ID from the URL

    // SQL query to delete the faculty assignment from the 'faculty_categories' table
    $sql = "DELETE FROM faculty_categories WHERE id = $faculty_category_id";

    // Execute the query
    if ($db->query($sql)) {
        // Success message and redirect to the faculty registration page
        $session->msg('s', 'Faculty assignment removed successfully.');
    } else {
        // Error message in case of failure
        $session->msg('d', 'Error: ' . $db->error);
    }
} else {
    // If the 'id' is not valid or not set, show an error message
    $session->msg('d', 'Invalid faculty assignment ID.');
}

// Redirect back to the previous page
redirect('management_faculty.php', false);
?>