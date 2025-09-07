<?php
require_once('includes/load.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input and get phone number
    $phone = remove_junk($db->escape($_POST['phone_number']));

    // Find the user by phone number
    $user = find_by_phone('users', $phone);  // Custom helper function

    if ($user) {
        // Generate a unique reset token
        $reset_token = bin2hex(random_bytes(16)); // 32-character token
        $token_expiration = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        // Store token in database
        $sql = "INSERT INTO user_password_resets (user_id, reset_token, token_expiration) 
                VALUES ('{$user['id']}', '{$reset_token}', '{$token_expiration}')";
        $db->query($sql);

        // Build the reset link
        $resetLink = "http://tmcshs.ct.ws/TMCSHS/School/reset_password.php?token={$reset_token}";

        try {
            // IPROG SMS API
            $url = 'https://sms.iprogtech.com/api/v1/sms_messages';
            $api_token = 'e98e51fe42029f522041591db234bc993deb5c43'; // Replace with actual token
            $phone_number = $user['phone_number'];
            $name = $user['name']; // Assuming `name` exists in your users table
            $message = sprintf("TMCSHS Notification: Hi %s, reset your password here (valid for 15 mins): %s", $name, $resetLink);

            $data = [
                'api_token'    => $api_token,
                'message'      => $message,
                'phone_number' => $phone_number
            ];

            // Initialize and send request
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            // Handle response
            $resp_data = json_decode($response, true);
            if (isset($resp_data['success']) && $resp_data['success']) {
                $msg = urlencode("Reset link sent via SMS.");
                redirect("forgot_password.php?success={$msg}", false);
            } else {
                $error = isset($resp_data['message']) ? $resp_data['message'] : 'Unknown error sending SMS.';
                $msg = urlencode("SMS error: {$error}");
                redirect("forgot_password.php?error={$msg}", false);
            }

        } catch (Exception $e) {
            $msg = urlencode("SMS exception: " . $e->getMessage());
            redirect("forgot_password.php?error={$msg}", false);
        }
    } else {
        // User not found
        $msg = urlencode("Phone number not found.");
        redirect("forgot_password.php?error={$msg}", false);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Helper function to find user by phone number
function find_by_phone($table, $phone) {
    global $db;
    $sql = "SELECT * FROM {$table} WHERE phone_number = '{$phone}' LIMIT 1";
    $result = $db->query($sql);
    return $db->fetch_assoc($result);
}
?>