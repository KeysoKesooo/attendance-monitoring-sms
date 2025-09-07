<?php

$page_title = 'Add Advisory';
require_once('includes/load.php');
page_require_level(1);

// Fetch all faculty members (users with user_level = 2)
$faculty_members = get_faculty_members();

// Fetch all categories (sections)
$categories = get_all_categories();

// Fetch categories that already have assigned faculty
$assigned_categories = get_assigned_categories();

// Fetch faculty members that are already assigned to sections
$assigned_faculty = get_assigned_faculty();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted values and sanitize
    $faculty_id = (int)$_POST['faculty_id'];  // Cast to integer
    $category_id = (int)$_POST['category_id']; // Cast to integer

    // Check if the section already has a faculty member assigned
    if (in_array($category_id, $assigned_categories)) {
        // Error message if section already has a faculty member
        $session->msg('d', 'Error: This section already has a faculty member assigned.');
        redirect('add_advisory.php', false);  // Redirect to the same page
    }

    // Check if the faculty member is already assigned to a section
    if (in_array($faculty_id, $assigned_faculty)) {
        // Error message if the faculty member is already assigned
        $session->msg('d', 'Error: This faculty member is already assigned to a section.');
        redirect('add_advisory.php', false);  // Redirect to the same page
    }

    // Insert the registration data into the faculty_categories table
    $sql = "INSERT INTO faculty_categories (user_id, categorie_id) VALUES ($faculty_id, $category_id)";
    
    // Execute the query
    if ($db->query($sql)) {
        // Success message
        $session->msg('s', 'Faculty assigned to section successfully!');
        redirect('add_advisory.php', false);  // Redirect to the same page
    } else {
        // Error message
        $session->msg('d', 'Error: ' . $db->error);
        redirect('add_advisory.php', false);  // Redirect to the same page
    }
}



include_once('layouts/header.php');
?>
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<button class="back_button" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="editpopup_container">
    <div class="editpopup_form_area">

        <?php if (isset($message)): ?>
        <div class="message">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="add_advisory.php" method="POST">
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="faculty_id">Select Faculty:</label>
                <select name="faculty_id" id="faculty_id" class="editpopup_form_style" required>
                    <option value="" disabled selected>Select Faculty</option>
                    <?php foreach ($faculty_members as $faculty): ?>
                    <option value="<?php echo $faculty['id']; ?>"
                        <?php echo (in_array($faculty['id'], $assigned_faculty)) ? 'disabled' : ''; ?>>
                        <?php echo $faculty['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="category_id">Select Section (Category):</label>
                <select name="category_id" id="category_id" class="editpopup_form_style" required>
                    <option value="" disabled selected>Select Section</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php echo (in_array($category['id'], $assigned_categories)) ? 'disabled' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="editpopup_btn">Register Faculty</button>
            </div>
        </form>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>