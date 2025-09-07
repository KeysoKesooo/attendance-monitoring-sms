<?php
require_once('includes/load.php');
require(__DIR__.'/../vendor/autoload.php');


$page_title = 'Add User';
page_require_level(1);
$groups = find_all('user_groups');

if (isset($_POST['add_user'])) {
    $req_fields = array('first_name', 'last_name', 'username', 'email', 'password', 'level');
    validate_fields($req_fields);

    if (empty($errors)) {
        $first_name = remove_junk($db->escape($_POST['first_name']));
        $middle_name = remove_junk($db->escape($_POST['middle_name']));
        $last_name   = remove_junk($db->escape($_POST['last_name']));

        // Optional suffix
        $suffix = isset($_POST['suffix']) ? remove_junk($db->escape($_POST['suffix'])) : '';

        // Build full name with middle name
        $name = $suffix ? "{$first_name} {$middle_name} {$last_name}, {$suffix}" : "{$first_name} {$middle_name} {$last_name}";
        $username = remove_junk($db->escape($_POST['username']));
        $email = remove_junk($db->escape($_POST['email']));
        $password = password_hash(remove_junk($db->escape($_POST['password'])), PASSWORD_BCRYPT);
        $user_level = (int)$db->escape($_POST['level']);
        
        $phone_number = null;
        if ($user_level === 3 && !empty($_POST['phone_number'])) {
            $raw_phone = remove_junk($db->escape($_POST['phone_number']));
            if (preg_match('/^09\d{9}$/', $raw_phone)) {
                // Convert 09123456789 → +639123456789
                $phone_number = '+63' . substr($raw_phone, 1);
            } else {
                $session->msg('d', 'Invalid phone number format. Please use 09123456789 format.');
                redirect('add_user.php', false);
            }
        }


        $email_exists = $db->query("SELECT id FROM users WHERE email = '{$email}' LIMIT 1");
        if ($db->num_rows($email_exists) > 0) {
            $session->msg('d', 'Email already exists. Please use a different email.');
            redirect('add_user.php', false);
        }

        if ($phone_number !== null) {
            $phone_exists = $db->query("SELECT id FROM users WHERE phone_number = '{$phone_number}' LIMIT 1");
            if ($db->num_rows($phone_exists) > 0) {
                $session->msg('d', 'Phone number already exists. Please use a different phone number.');
                redirect('add_user.php', false);
            }
        }

        $query = "INSERT INTO users (name, username, email, password, user_level, status";
        if ($phone_number !== null) {
            $query .= ", phone_number";
        }
        $query .= ") VALUES ('{$name}', '{$username}', '{$email}', '{$password}', '{$user_level}', '1'";
        if ($phone_number !== null) {
            $query .= ", '{$phone_number}'";
        }
        $query .= ")";

        if ($db->query($query)) {
            $user_id = $db->insert_id();

            if (!empty($_FILES['file_upload']['name'])) {
                $photo = new Media();
                $photo->upload($_FILES['file_upload']);
                $image_path = $photo->process_user($user_id);
            } else {
                $image_path = '/uploads/users/default.png';
            }
            
            $query = "INSERT INTO users (name, username, email, password, user_level, status, image) 
                      VALUES ('{$name}', '{$username}', '{$email}', '{$password}', '{$user_level}', '1', '{$image_path}')";
            
            // Run the query here (using $db->query or a custom function)
            
            $session->msg('s', "User account created!");
            

            if ($user_level === 3 && $phone_number !== null) {
                $message = sprintf(
                    "TMCSHS MANAGEMENT: Hi %s, your account has been created!\nUsername: %s\nPassword: %s",
                    $first_name,
                    $username,
                    $_POST['password'] // Note: avoid sending plaintext passwords in production
                );
            
                $sms_url = 'https://sms.iprogtech.com/api/v1/sms_messages';
            
                $sms_data = [
                    'api_token' => 'e98e51fe42029f522041591db234bc993deb5c43', // Replace with your actual IPROG token
                    'message' => $message,
                    'phone_number' => $phone_number // Ensure it's in the correct format (e.g. 639XXXXXXXXX)
                ];
            
                $ch = curl_init($sms_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded'
                ]);
            
                $response = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            
                if ($http_status == 200) {
                    $session->msg('s', "SMS sent to $first_name at $phone_number.");
                } else {
                    $session->msg('d', "Failed to send SMS. Response: $response");
                }
            }
            
            

            redirect('add_user.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to create account!');
            redirect('add_user.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_user.php', false);
    }
}

// CSV Import (remains unchanged for now)
if (isset($_POST['import_users'])) {
    $csv_file = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['error'] > 0) {
        $session->msg('d', 'Error uploading file.');
        redirect('add_user.php', false);
    }

    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        if ($header !== ['id', 'name', 'username', 'password', 'email', 'role', 'phone_number']) {
            $session->msg('d', 'Invalid CSV format.');
            redirect('add_user.php', false);
        }

        $db->begin();
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $full_name = remove_junk($db->escape($data[1]));
            $username = remove_junk($db->escape($data[2]));
            $password = password_hash(remove_junk($db->escape($data[3])), PASSWORD_BCRYPT);
            $email = remove_junk($db->escape($data[4]));
            $user_role = remove_junk($db->escape($data[5]));

            $user_level = 0;
            switch ($user_role) {
                case 'Admin': $user_level = 1; break;
                case 'Faculty': $user_level = 2; break;
                case 'Parent': $user_level = 3; break;
                default:
                    $session->msg('d', "Invalid role: $user_role for $full_name");
                    continue 2;
            }

            $phone_number = null;
            if ($user_level === 3) {
                $phone_number = remove_junk($db->escape($data[6]));
            }

            $query = "INSERT INTO users (name, username, password, email, user_level, status";
            if ($phone_number !== null) $query .= ", phone_number";
            $query .= ") VALUES ('{$full_name}', '{$username}', '{$password}', '{$email}', '{$user_level}', '1'";
            if ($phone_number !== null) $query .= ", '{$phone_number}'";
            $query .= ")";

            if (!$db->query($query)) {
                $db->rollback();
                $session->msg('d', "Failed to import user: $full_name");
                redirect('add_user.php', false);
            }
        }
        $db->commit();
        fclose($handle);
        $session->msg('s', "File imported successfully.");
    } else {
        $session->msg('d', 'Failed to open the CSV file.');
    }
    redirect('add_user.php', false);
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<button class="back_button" style="top: 20px; z-index:1; position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="editpopup_container">
    <div class="editpopup_form_area">
        <form method="post" action="add_user.php" enctype="multipart/form-data">
            <div class="editpopup_form_group">
                <div class="profile_con">
                    <img class="profile_img" src="/TMCSHS/images/defualt.png" alt="Profile Image">
                    <div class="img_text">
                        <input type="file" name="file_upload" class="text_img" accept="image/*">
                        <input type="hidden" name="image_status" value="/TMCSHS/images/defualt.png">
                        <h1>CHANGE PROFILE</h1>
                    </div>
                </div>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title">First Name</label>
                <input name="first_name" placeholder="First Name" class="editpopup_form_style" type="text" required>
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title">Middle Name</label>
                <input name="middle_name" placeholder="Middle Name" class="editpopup_form_style" type="text" required>
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title">Last Name</label>
                <input name="last_name" placeholder="Last Name" class="editpopup_form_style" type="text" required>
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="suffix">Suffix (optional)</label>
                <input name="suffix" placeholder="e.g., Jr., III" class="editpopup_form_style" type="text">
            </div>
            <div class="editpopup_form_group" id="usernameField">
                <label class="editpopup_sub_title">Username</label>
                <input name="username" placeholder="Username" class="editpopup_form_style" type="text" required>
            </div>
            <div class="editpopup_form_group" id="passwordField">
                <label class="editpopup_sub_title">Password</label>
                <input name="password" placeholder="Password" class="editpopup_form_style" type="text" required>
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title">Email</label>
                <input name="email" placeholder="Email Address" class="editpopup_form_style" type="email" required>
            </div>
            <div class="editpopup_form_group" id="phoneField" style="display: none;">
                <label class="editpopup_sub_title">Phone Number</label>
                <input name="phone_number" id="phone_number" placeholder="09123456789" class="editpopup_form_style"
                    type="text">
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title">User Role</label>
                <select class="editpopup_form_style" name="level" id="userRoleSelect" required>
                    <?php foreach ($groups as $group): ?>
                    <option value="<?php echo $group['group_level']; ?>"><?php echo ucwords($group['group_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" name="add_user" class="editpopup_btn">ADD</button>
            </div>
        </form>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const userRoleSelect = document.getElementById('userRoleSelect');
    const phoneField = document.getElementById('phoneField');

    userRoleSelect.addEventListener('change', function() {
        const selectedRole = parseInt(userRoleSelect.value, 10);
        phoneField.style.display = (selectedRole === 3) ? 'flex' : 'none'; // Show/Hide phone field
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const userRoleSelect = document.getElementById('userRoleSelect');
    const phoneField = document.getElementById('phoneField');
    const phoneInput = document.getElementById('phone_number');

    userRoleSelect.addEventListener('change', function() {
        const selectedRole = parseInt(userRoleSelect.value, 10);
        if (selectedRole === 3) {
            phoneField.style.display = 'flex';
            phoneInput.setAttribute('required', true); // Make phone number required
        } else {
            phoneField.style.display = 'none';
            phoneInput.removeAttribute('required'); // Remove required attribute
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[name="file_upload"]');
    const profileImg = document.querySelector('.profile_img');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include_once('layouts/footer.php'); ?>