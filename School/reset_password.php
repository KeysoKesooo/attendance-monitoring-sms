<?php
require_once('includes/load.php');

$token = isset($_GET['token']) ? remove_junk($db->escape($_GET['token'])) : '';
if (empty($token)) {
    // Redirect to login page with error message if token is invalid
    redirect("login.php?error=" . urlencode("Missing or invalid reset token."), false);
}

// Check if token exists in the database and is not expired
$sql = "SELECT * FROM user_password_resets WHERE reset_token = '{$token}' AND token_expiration > NOW() LIMIT 1";
$result = $db->query($sql);
if ($db->num_rows($result) === 0) {
    // Redirect to login page with error message if token is invalid or expired
    redirect("login.php?error=" . urlencode("Invalid or expired token."), false);
}

$row = $db->fetch_assoc($result);
$user_id = $row['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="libs/css/login.css" />
    <style>
    body {
        background: var(--main-bg-color);
        --main-bg-color: #F8FAFC;
        --primary-color: #B19470;
        --secondary-color: #780C28;
        --triary-color: #76453B;
    }

    body {
        margin: 0;
        padding: 0;
        background: url('images/school_bg.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .editpopup_container {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        text-align: center;
    }

    .editpopup_form_area {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        width: 100%;
    }

    .editpopup_title {
        color: var(--secondary-color);
        font-weight: 900;
        font-size: 1.8em;
        margin-bottom: 10px;
    }

    .editpopup_form_group {
        display: flex;
        flex-direction: column;
        margin: 15px 0;
        width: 100%;
    }

    .editpopup_form_group label {
        text-align: left;
        margin-left: 5px;
        font-weight: 600;
        color: var(--secondary-color);
    }

    .editpopup_form_style {
        outline: none;
        border: 2px solid var(--secondary-color);
        width: 300px;
        padding: 12px 10px;
        border-radius: 4px;
        font-size: 15px;
    }

    .editpopup_form_style:focus {
        box-shadow: 1px 2px 0px 0px var(--secondary-color);
        transform: translateY(2px);
    }

    .editpopup_btn {
        padding: 12px;
        margin-top: 20px;
        width: 300px;
        font-size: 15px;
        background: var(--secondary-color);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
    }

    .editpopup_btn:hover {
        background-color: #1a2e33;
    }

    .forgot-note {
        margin-top: 10px;
        font-size: 14px;
    }

    .forgot-note a {
        color: var(--secondary-color);
        text-decoration: none;
    }

    .forgot-note a:hover {
        text-decoration: underline;
    }

    .message {
        margin: 10px 0;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="editpopup_container">
        <div class="editpopup_title">Reset Your Password</div>

        <?php if (isset($_GET['error'])): ?>
        <div class="message" style="color:red;"><?php echo urldecode($_GET['error']); ?></div>
        <?php elseif (isset($_GET['success'])): ?>
        <div class="message" style="color:green;"><?php echo urldecode($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="editpopup_form_area">
            <form method="post" action="update_password.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>">

                <div class="editpopup_form_group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="editpopup_form_style" required>
                </div>

                <div class="editpopup_form_group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_passwopup_id=" confirm_password" class="editpopup_form_style"
                        required>
                </div>

                <button type="submit" class="editpopup_btn">Update Password</button>

                <div class="forgot-note">
                    <a href="logout.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>