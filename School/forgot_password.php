<?php
  ob_start();
  require_once('includes/load.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="libs/css/login.css" />
    <style>
    body {
        overflow-y: auto;

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
        color: var (secondary-color);
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
        <div class="editpopup_title">Forgot Password</div>

        <?php if (isset($_GET['error'])): ?>
        <div class="message" style="color:red;"><?php echo urldecode($_GET['error']); ?></div>
        <?php elseif (isset($_GET['success'])): ?>
        <div class="message" style="color:green;"><?php echo urldecode($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="editpopup_form_area">
            <form method="post" action="send_reset_token.php">
                <div class="editpopup_form_group">
                    <label for="phone">Phone Number</label>
                    <input name="phone_number" id="phone" placeholder="+63123456789" class="editpopup_form_style"
                        type="text" required>
                </div>

                <button type="submit" class="editpopup_btn">Send Reset Link</button>

                <div class="forgot-note">
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>