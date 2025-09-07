<?php
$page_title = 'Add Section';

require_once('includes/load.php');
page_require_level(1); // Check user permission level

$all_categories = find_all('categories');
?>

<?php
if(isset($_POST['add_cat'])){
    // Define the required fields (added 'strand')
    $req_field = array('section', 'grade_level', 'strand');
    validate_fields($req_field);

    // Retrieve and sanitize form input (added 'strand')
    $section = remove_junk($db->escape($_POST['section']));
    $grade_level = (int)remove_junk($db->escape($_POST['grade_level']));
    $strand = remove_junk($db->escape($_POST['strand'])); // New field

    if(empty($errors)){
        // Updated SQL query to include 'strand'
        $sql  = "INSERT INTO categories (name, grade_level, strand)";
        $sql .= " VALUES ('{$section}', '{$grade_level}', '{$strand}')";

        if($db->query($sql)){
            $session->msg("s", "Successfully Added New Section");
            redirect('add_section.php', false);
        } else {
            $session->msg("d", "Sorry, Failed to insert.");
            redirect('add_section.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_section.php', false);
    }
}
include_once('layouts/header.php');
?>

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
        <form method="post" action="add_section.php">
            <div class="editpopup_form_group" id="sectionField">
                <label class="editpopup_sub_title" for="section">SECTION</label>
                <input class="editpopup_form_style" type="text" id="section" name="section" required>
            </div>
            <div class="editpopup_form_group" id="gradelevelField">
                <label class="editpopup_sub_title" for="grade_level">Grade Level</label>
                <input class="editpopup_form_style" type="number" id="grade_level" name="grade_level" min="1" max="12"
                    required>
            </div>
            <!-- NEW: Strand Selection Dropdown -->
            <div class="editpopup_form_group" id="strandField">
                <label class="editpopup_sub_title" for="strand">Strand</label>
                <select class="editpopup_form_style" id="strand" name="strand" required>
                    <option value="">-- Select Strand --</option>
                    <option value="STEM">STEM</option>
                    <option value="ABM">ABM</option>
                    <option value="HUMSS">HUMSS</option>
                    <option value="GAS">GAS</option>
                    <option value="TVL">TVL</option>
                    <option value="GENERAL">GENERAL</option>
                </select>
            </div>
            <div>
                <button type="submit" name="add_cat" class="editpopup_btn">ADD</button>
            </div>
        </form>
    </div>
</div>


<?php include_once('layouts/footer.php'); ?>