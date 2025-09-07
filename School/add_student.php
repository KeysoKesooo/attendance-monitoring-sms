<?php
$page_title = 'Add Students';
require_once('includes/load.php');
page_require_level(1);

$all_categories = find_all('categories');
$all_photo = find_all('media');
$all_parents = find_by_sql("SELECT id, name, phone_number FROM users WHERE user_level = 3");

if (isset($_POST['add_student'])) {
    $req_fields = array('first_name', 'middle_name', 'last_name', 'section', 'grade_level', 'strand', 'phone_id', 'address', 'student_username', 'student_password');
    validate_fields($req_fields);

    if (empty($errors)) {
        $first_name  = remove_junk($db->escape($_POST['first_name']));
        $middle_name = remove_junk($db->escape($_POST['middle_name']));
        $last_name   = remove_junk($db->escape($_POST['last_name']));

        // Optional suffix
        $suffix = isset($_POST['suffix']) ? remove_junk($db->escape($_POST['suffix'])) : '';

        // Build full name with middle name
        $p_name = $suffix ? "{$first_name} {$middle_name} {$last_name}, {$suffix}" : "{$first_name} {$middle_name} {$last_name}";

        
        $p_gender = remove_junk($db->escape($_POST['gender']));
        $p_cat = remove_junk($db->escape($_POST['section']));
        $c_grade_level = remove_junk($db->escape($_POST['grade_level']));
        $c_strand = remove_junk($db->escape($_POST['strand']));
        $phone_id = (int)$_POST['phone_id'];
        $p_address = remove_junk($db->escape($_POST['address']));
        $p_username = remove_junk($db->escape($_POST['student_username']));
        $p_password = password_hash(remove_junk($db->escape($_POST['student_password'])), PASSWORD_BCRYPT);
        $date = make_date();
        $student_image = '';


        // Check if username already exists in users table
        $user_exists = find_by_sql("SELECT * FROM users WHERE username = '{$p_username}'");
        if (!empty($user_exists)) {
            $session->msg('d', 'Username already exists!');
            redirect('add_student.php', false);
        }

        // Insert into users table for login
        $insert_user = "INSERT INTO users (name, username, password, user_level, status) VALUES ('{$p_name}', '{$p_username}', '{$p_password}', 4, 1)";
        if ($db->query($insert_user)) {
            $user_id = $db->insert_id(); // Get the user ID

            // Insert into student table with the user_id
            $query  = "INSERT INTO student (name, gender, categorie_id, phone_id, address, student_image, date, student_username, student_password, user_id)";
            $query .= " VALUES ('{$p_name}', '{$p_gender}', '{$p_cat}', '{$phone_id}', '{$p_address}', '{$student_image}', '{$date}', '{$p_username}', '{$p_password}', '{$user_id}')";

            if ($db->query($query)) {
                $id = $db->insert_id(); // Get the student ID

                // Process photo upload
                if (!empty($_FILES['student-photo']['name'])) {
                    $photo = new Media();
                    $photo->upload($_FILES['student-photo']);
                    if ($photo->process_student($id)) {
                        $student_image = $photo->fileName;
                        $update_query = "UPDATE student SET student_image = '{$student_image}' WHERE id = {$id}";
                        $db->query($update_query);
                    } else {
                        $session->msg('d', "Student created, but failed to upload the photo: " . join($photo->errors));
                        redirect('add_student.php', false);
                    }
                }

                $session->msg('s', "Student added successfully!");
                redirect('add_student.php', false);
            } else {
                $session->msg('d', 'Sorry, failed to add student!');
                redirect('add_student.php', false);
            }
        } else {
            $session->msg('d', 'Failed to create user account!');
            redirect('add_student.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_student.php', false);
    }
}

include_once('layouts/header.php');
?>

<!-- FRONTEND HTML -->
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
        <form method="post" action="add_student.php" enctype="multipart/form-data">
            <div class="editpopup_form_group">
                <div class="profile_con">
                    <img class="profile_img" src="/test1/images/defualt.png" alt="Profile Image">
                    <div class="img_text">
                        <input type="file" name="student-photo" class="text_img" accept="image/*">
                        <h1>CHANGE PROFILE</h1>
                    </div>
                </div>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="first_name">First Name</label>
                <input name="first_name" placeholder="First Name" class="editpopup_form_style" type="text" required>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="middle_name">Middle Name</label>
                <input name="middle_name" placeholder="Middle Name" class="editpopup_form_style" type="text" required>
            </div>


            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="last_name">Last Name</label>
                <input name="last_name" placeholder="Last Name" class="editpopup_form_style" type="text" required>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="suffix">Suffix (optional)</label>
                <input name="suffix" placeholder="e.g., Jr., III" class="editpopup_form_style" type="text">
            </div>


            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="student_username">Username</label>
                <input name="student_username" placeholder="Username" class="editpopup_form_style" type="text" required>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="student_password">Password</label>
                <input name="student_password" placeholder="Password" class="editpopup_form_style" type="password"
                    required>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="gender">Gender</label>
                <select class="editpopup_form_style" id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>

            <div class="editpopup_form_group" id="sectionField">
                <label class="editpopup_sub_title" for="section">SECTION</label>
                <select class="editpopup_form_style" id="section" name="section" required>
                    <option value="">Select Section</option>
                    <?php foreach ($all_categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id'] ?>" data-grade="<?php echo (int)$cat['grade_level']; ?>"
                        data-strand="<?php echo $cat['strand']; ?>">
                        <?php echo $cat['name'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="editpopup_form_group" id="gradelevelField">
                <label class="editpopup_sub_title" for="grade_level">Grade Level</label>
                <select class="editpopup_form_style" id="grade_level" name="grade_level" required>
                    <option value="">Grade Level</option>
                </select>
            </div>

            <div class="editpopup_form_group" id="strandField">
                <label class="editpopup_sub_title" for="strand">Strand</label>
                <select class="editpopup_form_style" id="strand" name="strand" required>
                    <option value="">Select Strand</option>
                </select>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="address">Address</label>
                <input name="address" placeholder="Address" class="editpopup_form_style" type="text" required>
            </div>

            <div class="editpopup_form_group" id="parentField">
                <label class="editpopup_sub_title" for="phone_id">Parent's Phone Number</label>
                <select class="editpopup_form_style" id="phone_id" name="phone_id" required>
                    <option value="">Select Parent's Phone Number</option>
                    <?php foreach ($all_parents as $parent): ?>
                    <option value="<?php echo (int)$parent['id'] ?>"><?php echo $parent['name'] ?> -
                        <?php echo $parent['phone_number'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" name="add_student" class="editpopup_btn">ADD</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionSelect = document.getElementById('section');
    const gradeLevelSelect = document.getElementById('grade_level');
    const strandSelect = document.getElementById('strand');

    sectionSelect.addEventListener('change', function() {
        const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
        const gradeLevel = selectedOption.getAttribute('data-grade');
        const strand = selectedOption.getAttribute('data-strand');

        // Update Grade Level
        gradeLevelSelect.innerHTML = '<option value="">Grade Level</option>';
        if (gradeLevel) {
            const gradeOption = document.createElement('option');
            gradeOption.value = gradeLevel;
            gradeOption.textContent = gradeLevel;
            gradeLevelSelect.appendChild(gradeOption);
        }

        // Update Strand
        strandSelect.innerHTML = '<option value="">Select Strand</option>';
        if (strand) {
            const strandOption = document.createElement('option');
            strandOption.value = strand;
            strandOption.textContent = strand;
            strandSelect.appendChild(strandOption);
        }
    });

    // Photo upload preview
    const fileInput = document.querySelector('input[name="student-photo"]');
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