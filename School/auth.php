<?php 
include_once('includes/load.php'); 

$req_fields = array('username', 'password');
validate_fields($req_fields);

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
    // Authenticate the user
    $user_id = authenticate($username, $password);
    if ($user_id) {
        // Fetch user details
        $user = find_by_id('users', $user_id); // Assuming this function fetches user data by ID
        $user_level = $user['user_level'];  // Get the user level
        
        // Create session with id
        $session->login($user_id);
        
        // Update sign-in time
        updateLastLogIn($user_id);
        
        // Set success message
        $session->msg("s", "Welcome to Methanoiah Academy");

        // Redirect based on user level
        if ($user_level == 1) {
            redirect('dashboard.php', false); // Admin
        } elseif ($user_level == 2) {
            redirect('dashboard_faculty.php', false); // Faculty
        } elseif ($user_level == 3) {
            redirect('dashboard_parent.php', false); // Parent
        } elseif ($user_level == 4) {
            redirect('dashboard_student.php', false); // Student
        } else {
            $session->msg("d", "Invalid user level.");
            redirect('login.php', false);
        }
    } else {
        $session->msg("d", "Sorry, Username/Password is incorrect.");
        redirect('login.php', false);
    }
} else {
    $session->msg("d", $errors);
    redirect('login.php', false);
}