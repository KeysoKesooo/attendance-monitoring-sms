<?php

require_once('includes/load.php');
$page_title = 'Edit Student';

// Check user level permissions
page_require_level(2);

$student = find_by_id('student', (int)$_GET['id']);
$gender = find_all('gender');
$all_categories = find_all('categories');
$all_photo = find_all('media');
$all_parents = find_by_sql("SELECT id, name, phone_number FROM users WHERE user_level = 3");

if (!$student) {
    $session->msg("d", "Missing student id.");
    redirect('edit_student.php');
}

if (isset($_POST['edit_student'])) {
    $req_fields = array( 'strand', 'section', 'grade_level', 'phone_id');
    validate_fields($req_fields);

    if (empty($errors)) {
        $first_name = remove_junk($db->escape($_POST['first_name']));
        $middle_name = remove_junk($db->escape($_POST['middle_name']));
        $last_name = remove_junk($db->escape($_POST['last_name']));
        
        $suffix = isset($_POST['suffix']) ? remove_junk($db->escape($_POST['suffix'])) : '';
        $p_name = $suffix ? "{$first_name} {$middle_name} {$last_name}, {$suffix}" : "{$first_name} {$middle_name} {$last_name}";


        $p_gender = remove_junk($db->escape($_POST['gender']));
        $p_strand = remove_junk($db->escape($_POST['strand']));
        $p_cat = remove_junk($db->escape($_POST['section']));
        $p_qty = remove_junk($db->escape($_POST['grade_level']));
        $phone_id = (int)$_POST['phone_id'];

        if (!empty($_FILES['student-photo']['name'])) {
            $photo = new Media();
            $photo->upload($_FILES['student-photo']);
            if ($photo->process_student($student['id'])) {
                $student_image = $photo->fileName;
            } else {
                $session->msg('d', join($photo->errors));
            }
        } else {
            $student_image = $student['student_image'];
        }

        $query = "UPDATE student SET";
        $query .= " name = '{$p_name}',";
        $query .= " gender = '{$p_gender}',";
        $query .= " categorie_id = '{$p_cat}',";
        $query .= " phone_id = '{$phone_id}',";
        $query .= " student_image = '{$student_image}'";
        $query .= " WHERE id = '{$student['id']}'";

        $result = $db->query($query);

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Student updated successfully");
            redirect('management_student.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('management_student.php?id=' . $student['id'], false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('management_student.php?id=' . $student['id'], false);
    }
}

include_once('layouts/header.php');
?>

<?php echo display_msg($msg); ?>

<button class="back_button" onclick="window.history.back()">
    <!-- SVG BACK ICON -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div id="editproduct" class="overlays">
    <div class="editpopup_container">
        <div class="editpopup_form_area">
            <form method="post" action="edit_student.php?id=<?php echo (int)$student['id']; ?>"
                enctype="multipart/form-data">
                <div class="editpopup_form_group">
                    <div class="profile_con">
                        <img class="profile_img" src="/test1/images/defualt.png" alt="Profile Image">
                        <div class="img_text">
                            <input type="file" name="student-photo" class="text_img" accept="image/*">
                            <h1>CHANGE PROFILE</h1>
                        </div>
                    </div>
                </div>

                <!-- First and Last Name -->
                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title">First Name</label>
                    <input type="text" name="first_name" class="editpopup_form_style" placeholder="First Name"
                        value="<?php echo explode(' ', $student['name'])[0]; ?>">
                </div>

                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title">Middle Name</label>
                    <input type="text" name="middle_name" class="editpopup_form_style" placeholder="Middle Name"
                        value="<?php echo isset(explode(' ', $student['name'])[1]) ? explode(' ', $student['name'])[1] : ''; ?>">
                </div>


                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title">Last Name</label>
                    <input type="text" name="last_name" class="editpopup_form_style" placeholder="Last Name"
                        value="<?php echo isset(explode(' ', $student['name'])[2]) ? explode(' ', $student['name'])[2] : ''; ?>">
                </div>


                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="suffix">Suffix (optional)</label>
                    <input name="suffix" placeholder="e.g., Jr., III" class="editpopup_form_style" type="text">
                </div>


                <!-- Gender -->
                <div class="editpopup_form_group">
                    <label class="editpopup_sub_title" for="gender">Gender</label>
                    <select class="editpopup_form_style" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php if($student['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($student['gender'] == 'Female') echo 'selected'; ?>>Female
                        </option>
                    </select>
                </div>

                <!-- Section Dropdown (Now includes data-strand) -->
                <div class="editpopup_form_group" id="sectionField">
                    <label class="editpopup_sub_title" for="section">SECTION</label>
                    <select class="editpopup_form_style" id="section" name="section" required>
                        <option value="">Select Section</option>
                        <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id'] ?>"
                            data-grade="<?php echo (int)$cat['grade_level']; ?>"
                            data-strand="<?php echo $cat['strand']; ?>">
                            <?php echo $cat['name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Grade Level (Auto-populated) -->
                <div class="editpopup_form_group" id="gradelevelField">
                    <label class="editpopup_sub_title" for="grade_level">Grade Level</label>
                    <select class="editpopup_form_style" id="grade_level" name="grade_level" required>
                        <option value="">Grade Level</option>
                    </select>
                </div>

                <!-- Strand Field (Auto-populated) -->
                <div class="editpopup_form_group" id="strandField">
                    <label class="editpopup_sub_title" for="strand">Strand</label>
                    <select class="editpopup_form_style" id="strand" name="strand" required>
                        <option value="">Select Strand</option>
                    </select>
                </div>

                <!-- Parent's Phone Number -->
                <div class="editpopup_form_group" id="parentField">
                    <label class="editpopup_sub_title" for="phone_id">Parent's Phone Number</label>
                    <select class="editpopup_form_style" id="phone_id" name="phone_id" required>
                        <option value="">Select Parent's Phone Number</option>
                        <?php foreach ($all_parents as $parent): ?>
                        <option value="<?php echo (int)$parent['id'] ?>"
                            <?php echo ($parent['id'] === (int)$student['phone_id']) ? 'selected' : ''; ?>>
                            <?php echo $parent['name'] ?> - <?php echo $parent['phone_number'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <button type="submit" name="edit_student" class="editpopup_btn">UPDATE</button>
                </div>
            </form>
        </div>
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

    // Photo upload preview (unchanged)
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