<?php
require_once('includes/load.php');
$page_title = 'Edit User';
page_require_level(1);

// Get user data
$e_user = find_by_id('users', (int)$_GET['id']);
$groups = find_all('user_groups');

if (!$e_user) {
    $session->msg("d", "Missing user id.");
    redirect('users.php');
}

// Parse full name into components if needed
$name_parts = explode(' ', $e_user['name']);
$first_name = $name_parts[0] ?? '';
$middle_name = $name_parts[1] ?? '';
$last_name = $name_parts[2] ?? '';
$suffix = '';

// Check for suffix in the name
if (strpos($e_user['name'], ',') !== false) {
    $name_with_suffix = explode(',', $e_user['name']);
    $suffix = trim($name_with_suffix[1] ?? '');
    $name_without_suffix = trim($name_with_suffix[0]);
    $name_parts = explode(' ', $name_without_suffix);
    $first_name = $name_parts[0] ?? '';
    $middle_name = $name_parts[1] ?? '';
    $last_name = $name_parts[2] ?? '';
}

// Update User basic info and photo
if (isset($_POST['update'])) {
    $req_fields = array('first_name', 'last_name', 'username');
    validate_fields($req_fields);

    if (empty($errors)) {
        $id = (int)$e_user['id'];
        $first_name = remove_junk($db->escape($_POST['first_name']));
        $middle_name = remove_junk($db->escape($_POST['middle_name'] ?? ''));
        $last_name = remove_junk($db->escape($_POST['last_name']));
        $suffix = isset($_POST['suffix']) ? remove_junk($db->escape($_POST['suffix'])) : '';
        
        // Build full name
        $name = trim("{$first_name} {$middle_name} {$last_name}");
        if (!empty($suffix)) {
            $name .= ", {$suffix}";
        }
        
        $username = remove_junk($db->escape($_POST['username']));
        $status = remove_junk($db->escape($_POST['status']));
        $user_level = (int)$db->escape($_POST['level']);
        $phone_number = remove_junk($db->escape($_POST['phone_number'] ?? ''));
        $update_image = false;

        // Process image upload only if a new image is provided
        if (isset($_FILES['user-photo']) && $_FILES['user-photo']['size'] > 0) {
            $photo = new Media();
            $photo->upload($_FILES['user-photo']);
            if ($photo->process_user($id)) {
                $update_image = true;
            } else {
                $session->msg('d', join($photo->errors));
            }
        }

        // Build the SQL update query with only changed fields
        $updates = array();
        
        // Only update name if it's different
        if ($name !== $e_user['name']) {
            $updates[] = "name = '{$name}'";
        }
        
        // Only update username if it's different
        if ($username !== $e_user['username']) {
            $updates[] = "username = '{$username}'";
        }
        
        // Only update user level if it's different
        if ($user_level != $e_user['user_level']) {
            $updates[] = "user_level = '{$user_level}'";
        }
        
        // Only update status if it's different
        if ($status != $e_user['status']) {
            $updates[] = "status = '{$status}'";
        }
        
        // Only update phone number if it's different and not empty
        if (isset($_POST['phone_number']) && $phone_number != $e_user['phone_number']) {
            $updates[] = "phone_number = '{$phone_number}'";
        }

        // If there are updates to make
        if (!empty($updates)) {
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = '{$db->escape($id)}'";
            $result = $db->query($sql);
        } else {
            // No changes to basic info
            $result = true;
        }

        // Password change (optional)
        if (!empty($_POST['password']) && !empty($_POST['new-password'])) {
            $old_password = $_POST['password'];
            $new_password = $_POST['new-password'];

            if (!password_verify($old_password, $e_user['password'])) {
                $session->msg('d', "The old password does not match.");
            } else {
                $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $sql_password = "UPDATE users SET password = '{$db->escape($new_hashed_password)}' WHERE id = '{$id}'";
                $result_password = $db->query($sql_password);

                if($result_password && $db->affected_rows() === 1) {
                    if($current_user['id'] === $e_user['id']) {
                        $session->logout();
                        $session->msg('s', "Password changed. Please login with your new password.");
                        redirect('index.php', false);
                    } else {
                        $session->msg('s', "Password updated successfully.");
                    }
                }
            }
        }

        if ($result) {
            $msg = '';
            if ($update_image) {
                $msg = "User photo updated successfully.";
                if (!empty($updates)) {
                    $msg = "User account and photo updated successfully.";
                }
            } elseif (!empty($updates)) {
                $msg = "User account updated successfully.";
            }
            
            if (!empty($msg)) {
                $session->msg('s', $msg);
            }
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
        }
    } else {
        $session->msg('d', $errors);
        redirect('edit_user.php?id='.(int)$e_user['id'], false);
    }
}
?>

<!-- The rest of your HTML remains the same -->
<?php include_once('layouts/header.php'); ?>
<?php echo display_msg($msg); ?>

<button class="back_button" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div id="edituser" class="overlays">
    <div class="editpopup_container">
        <div class="editpopup_form_area">
            <form method="post" action="edit_user.php?id=<?php echo (int)$e_user['id']; ?>"
                enctype="multipart/form-data">
                <div class="editpopup_form_group">
                    <div class="profile_con">
                        <img class="profile_img" src="uploads/images/<?php echo $e_user['image']; ?>"
                            alt="Profile Image">
                        <div class="img_text">
                            <input type="file" name="user-photo" class="text_img" accept="image/*">
                            <h1>CHANGE PROFILE</h1>
                        </div>
                    </div>
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="first_name">First Name</label>
                    <input name="first_name" placeholder="First Name" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($first_name); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="middle_name">Middle Name</label>
                    <input name="middle_name" placeholder="Middle Name" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($middle_name); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="last_name">Last Name</label>
                    <input name="last_name" placeholder="Last Name" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($last_name); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="suffix">Suffix (optional)</label>
                    <input name="suffix" placeholder="e.g., Jr., III" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($suffix); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="username">Username</label>
                    <input name="username" placeholder="Username" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($e_user['username']); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="password">Old Password</label>
                    <input name="password" placeholder="Leave blank to keep current" class="editpopup_form_style"
                        type="password">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="new-password">New Password</label>
                    <input name="new-password" placeholder="Leave blank to keep current" class="editpopup_form_style"
                        type="password">
                </div>

                <div class="editpopup_form_group" id="phoneField">
                    <label class="editpopup_sub_title" for="phone_number">Phone Number</label>
                    <input name="phone_number" placeholder="09123456789" class="editpopup_form_style" type="text"
                        value="<?php echo remove_junk($e_user['phone_number']); ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="level">User Role</label>
                    <select class="editpopup_form_style" name="level" id="userRoleSelect">
                        <?php foreach ($groups as $group): ?>
                        <option
                            <?php if ($group['group_level'] === $e_user['user_level']) echo 'selected="selected"'; ?>
                            value="<?php echo $group['group_level']; ?>">
                            <?php echo ucwords($group['group_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="status">Status</label>
                    <select class="editpopup_form_style" name="status">
                        <option <?php if ($e_user['status'] === '1') echo 'selected="selected"'; ?> value="1">Active
                        </option>
                        <option <?php if ($e_user['status'] === '0') echo 'selected="selected"'; ?> value="0">Inactive
                        </option>
                    </select>
                </div>

                <div>
                    <button type="submit" name="update" class="editpopup_btn">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle phone field visibility based on user role
    const userRoleSelect = document.getElementById('userRoleSelect');
    const phoneField = document.getElementById('phoneField');
    const phoneInput = phoneField.querySelector('input');

    function checkRole() {
        const selectedRole = parseInt(userRoleSelect.value, 10);
        if (selectedRole === 3) { // Assuming 3 is the role that requires phone
            phoneField.style.display = 'flex';
            phoneInput.setAttribute('required', true);
        } else {
            phoneField.style.display = 'none';
            phoneInput.removeAttribute('required');
        }
    }

    // Initial check
    checkRole();

    // Event listener for role changes
    userRoleSelect.addEventListener('change', checkRole);

    // Handle profile image preview
    const fileInput = document.querySelector('input[name="user-photo"]');
    const profileImg = document.querySelector('.profile_img');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type.match('image.*')) {
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