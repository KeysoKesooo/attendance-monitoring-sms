<?php
require_once('includes/load.php');
$page_title = 'Edit Section';
page_require_level(1); // Check user permission level

// Fetch the category to edit
$categorie = find_by_id('categories', (int)$_GET['id']);
if (!$categorie) {
    $session->msg("d", "Missing category id.");
    redirect('management_sections.php');
}
?>

<?php
if (isset($_POST['edit_cat'])) {
    // Define required fields (added 'grade_level' and 'strand')
    $req_field = array('name', 'grade_level', 'strand');
    validate_fields($req_field);

    // Sanitize inputs
    $name = remove_junk($db->escape($_POST['name']));
    $grade_level = (int)remove_junk($db->escape($_POST['grade_level']));
    $strand = remove_junk($db->escape($_POST['strand']));

    if (empty($errors)) {
        // Update SQL query to include all fields
        $sql = "UPDATE categories SET 
                name='{$name}',
                grade_level='{$grade_level}',
                strand='{$strand}'
                WHERE id='{$categorie['id']}'";

        $result = $db->query($sql);
        if ($result && $db->affected_rows() === 1) {
            $session->msg("s", "Successfully updated section");
            redirect('management_sections.php', false);
        } else {
            $session->msg("d", "No changes made or failed to update");
            redirect('management_sections.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('management_sections.php', false);
    }
}
?>
<?php include_once('layouts/header.php'); ?>

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
        <form method="post" action="edit_categorie.php?id=<?php echo (int)$categorie['id']; ?>">
            <!-- Section Name -->
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="name">Section Name</label>
                <input name="name" class="editpopup_form_style" type="text"
                    value="<?php echo remove_junk($categorie['name']); ?>" required>
            </div>

            <!-- Grade Level (1-12) -->
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="grade_level">Grade Level</label>
                <input name="grade_level" class="editpopup_form_style" type="number"
                    value="<?php echo (int)$categorie['grade_level']; ?>" min="1" max="12" required>
            </div>

            <!-- Strand Dropdown -->
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="strand">Strand</label>
                <select name="strand" class="editpopup_form_style" required>
                    <option value="">-- Select Strand --</option>
                    <option value="STEM" <?php if ($categorie['strand'] === 'STEM') echo 'selected'; ?>>STEM</option>
                    <option value="ABM" <?php if ($categorie['strand'] === 'ABM') echo 'selected'; ?>>ABM</option>
                    <option value="HUMSS" <?php if ($categorie['strand'] === 'HUMSS') echo 'selected'; ?>>HUMSS</option>
                    <option value="GAS" <?php if ($categorie['strand'] === 'GAS') echo 'selected'; ?>>GAS</option>
                    <option value="TVL" <?php if ($categorie['strand'] === 'TVL') echo 'selected'; ?>>TVL</option>
                    <option value="GENERAL" <?php if ($categorie['strand'] === 'GENERAL') echo 'selected'; ?>>GENERAL
                    </option>
                </select>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" name="edit_cat" class="editpopup_btn">UPDATE</button>
            </div>
        </form>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>