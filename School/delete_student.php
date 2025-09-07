<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(1);

// Get the student
$student = find_by_id('student', (int)$_GET['id']);
if(!$student) {
    $session->msg("d", "Missing Student ID.");
    redirect('management_student.php');
}

// Start transaction for atomic operation
$db->query("START TRANSACTION");

// First delete from student table
$delete_student = delete_by_id('student', (int)$student['id']);

if($delete_student) {
    // Then delete from users table
    $delete_user = delete_by_id('users', (int)$student['user_id']);
    
    if($delete_user) {
        $db->query("COMMIT");
        $session->msg("s", "Student deleted successfully.");
    } else {
        $db->query("ROLLBACK");
        $session->msg("d", "Student record deleted but user account deletion failed.");
    }
} else {
    $db->query("ROLLBACK");
    $session->msg("d", "Student deletion failed.");
}

redirect('management_student.php');
?>