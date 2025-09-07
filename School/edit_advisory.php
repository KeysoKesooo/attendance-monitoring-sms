<?php
$page_title = 'Edit Faculty Advisory';
require_once('includes/load.php');
page_require_level(1);

// Fetch the faculty category ID from the URL
if (isset($_GET['id'])) {
    $faculty_category_id = (int)$_GET['id'];

    // Fetch the existing faculty category details using the find_faculty_categories function
    $faculty_categories = find_faculty_categories();

    // Find the current faculty category by ID
    $faculty_category = null;
    foreach ($faculty_categories as $category) {
        if ($category['faculty_category_id'] == $faculty_category_id) {
            $faculty_category = $category;
            break;
        }
    }

    if (!$faculty_category) {
        // Error if faculty category ID is invalid
        $session->msg('d', 'Faculty Category not found.');
        redirect('faculty_list.php');
    }

    // Get the assigned faculty sections using get_faculty_sections function
    $faculty_sections = get_faculty_sections($faculty_category['faculty_category_id']);

    // Get all available categories (sections)
    $categories = find_all('categories');

    // Fetch all faculty members (users with user_level = 2)
    $faculty_members = get_faculty_members();
} else {
    // Error if no faculty category ID is provided
    $session->msg('d', 'Missing Faculty Section ID.');
    redirect('management_faculty.php');
}

// Fetch categories that already have assigned faculty
$assigned_categories = get_assigned_categories();

// Fetch faculty members that are already assigned to sections
$assigned_faculty = get_assigned_faculty();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty_id = (int)$_POST['faculty_id'];
    $category_id = (int)$_POST['category_id'];

    // Check if the section already has a faculty member assigned
    if (in_array($category_id, $assigned_categories)) {
        // Error message if section already has a faculty member
        $session->msg('d', 'Error: This section already has a faculty member assigned.');
        redirect("edit_advisory.php?id={$faculty_category_id}", false); // Redirect back
    }

    // Check if the faculty member is already assigned to a section
    if (in_array($faculty_id, $assigned_faculty)) {
        // Error message if the faculty member is already assigned
        $session->msg('d', 'Error: This faculty member is already assigned to a section.');
        redirect("edit_advisory.php?id={$faculty_category_id}", false); // Redirect back
    }

    // Update the faculty category assignment using 'id' as the primary key
    $sql = "UPDATE faculty_categories SET categorie_id = $category_id WHERE id = $faculty_category_id";

    if ($db->query($sql)) {
        // Success message
        $session->msg('s', 'Faculty assignment updated successfully!');
        redirect('management_faculty.php', false);  // Redirect to management page
    } else {
        // Error message
        $session->msg('d', 'Error: ' . $db->error);
        redirect("edit_advisory.php?id={$faculty_category_id}", false);  // Redirect back
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

<form action="edit_advisory.php?id=<?php echo $faculty_category_id; ?>" method="POST">
    <div class="editpopup_container">
        <div class="editpopup_form_area">

            <?php if (isset($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="faculty_id">Assigned Faculty:</label>
                <input type="text" class="editpopup_form_style readonly"
                    value="<?php echo $faculty_category['faculty_name']; ?>" readonly />
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="category_id">Select Section:</label>
                <select name="category_id" id="category_id" class="editpopup_form_style" required>
                    <option value="" disabled>Select Section</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php echo ($category['id'] == $faculty_category['categorie_id']) ? 'selected' : ''; // Pre-select the assigned section ?>
                        <?php echo (in_array($category['id'], $assigned_categories)) ? 'disabled' : ''; // Disable already assigned categories ?>>
                        <?php echo $category['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div>
                <button type="submit" class="editpopup_btn">Update Faculty Assignment</button>
            </div>

        </div>
    </div>
</form>

<?php include_once('layouts/footer.php'); ?>