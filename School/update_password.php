<?php
require_once('includes/load.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = remove_junk($db->escape($_POST['token']));
    $new_pass = remove_junk($db->escape($_POST['new_password']));
    $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT); // Use bcrypt hashing

    $now = date("Y-m-d H:i:s");

    // Validate token
    $sql = "SELECT * FROM user_password_resets WHERE reset_token = '{$token}' AND token_expiration > '{$now}' LIMIT 1";
    $result = $db->query($sql);

    if ($db->num_rows($result) === 1) {
        $reset = $db->fetch_assoc($result);
        $user_id = $reset['user_id'];

        // Update the user's password
        $sql_update = "UPDATE users SET password = '{$hashed_pass}' WHERE id = '{$user_id}'";
        $db->query($sql_update);

        // Delete the used token
        $db->query("DELETE FROM user_password_resets WHERE user_id = '{$user_id}'");

        redirect("index.php?success=" . urlencode("Password reset successful."), false);
    } else {
        redirect("reset_password.php?error=" . urlencode("Invalid or expired token."), false);
    }
}
?>