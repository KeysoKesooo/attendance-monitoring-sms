<?php
  $page_title = 'All User';
  require_once('includes/load.php');
?>
<?php
// Checkin What level user has permission to view this page
 page_require_level(1);
//pull out all user form database
 $all_users = find_all_user();
 
?>



<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" type="" href="libs/css/roles.css" />
</head>


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

<div class="col-md-12">

    <div class="search-bar-container">
        <input type="text" name="text" class="search-bar" placeholder="search...">
        <span class="search-bar-icon">
            <svg width="19px" height="19px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path opacity="1" d="M14 5H20" stroke="#000" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                    <path opacity="1" d="M14 8H17" stroke="#000" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                    <path d="M21 11.5C21 16.75 16.75 21 11.5 21C6.25 21 2 16.75 2 11.5C2 6.25 6.25 2 11.5 2"
                        stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path opacity="1" d="M22 22L20 20" stroke="#000" stroke-width="3.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </g>
            </svg>
        </span>
    </div>
    <a type="add_button" class="add_button" href="#adduser">
        <span class="add_button__text">Add Item</span>
        <span class="add_button__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24"
                stroke-width="2" stroke-linejoin="round" stroke-linecap="round" stroke="currentColor" height="24"
                fill="none" class="svg">
                <line y2="19" y1="5" x2="12" x1="12"></line>
                <line y2="12" y1="12" x2="19" x1="5"></line>
            </svg></span>
    </a>
    <div class="table">
        <div class="table-header">
            <div class="header__item"><a id="id" class="filter__link filter__link--number" href="#">ID</a></div>
            <div class="header__item"><a id="name" class="filter__link" href="#">Name</a></div>
            <div class="header__item"><a id="username" class="filter__link" href="#">Username</a></div>
            <div class="header__item"><a id="p_number" class="filter__link" href="#">Phone Number</a></div>
            <div class="header__item"><a id="userrole" class="filter__link" href="#">User Role</a>
            </div>
            <div class="header__item"><a class="filter__link" href="#">Status</a>
            </div>
            <div class="header__item"><a id="date" class="filter__link filter__link--number" href="#">Last
                    Login</a>
            </div>
            <div class="header__item"><a class="filter__link" href="#">Actions</a>
            </div>
        </div>
        <div class="table-content">
            <?php foreach($all_users as $a_user): ?>
            <div class="table-row">
                <div class="table-data"><?php echo count_id();?></div>
                <div class="table-data"><?php echo remove_junk(ucwords($a_user['name']))?></div>
                <div class="table-data"><?php echo remove_junk(ucwords($a_user['username']))?></div>
                <div class="table-data"><?php echo remove_junk(ucwords($a_user['phone_number']))?></div>
                <div class="table-data"><?php echo remove_junk(ucwords($a_user['group_name']))?></div>
                <div class="table-data">
                    <?php if($a_user['status'] === '1'): ?>
                    <span class="label label-success"><?php echo "Active"; ?></span>
                    <?php else: ?>
                    <span class="label label-danger"><?php echo "Deactive"; ?></span>
                    <?php endif;?>
                </div>
                <div class="table-data"><?php echo read_date($a_user['last_login'])?></div>

                <div class="table-data">
                    <a href="edit_user.php?id=<?php echo (int)$a_user['id'];?>" class="btn btn-xs btn-warning"
                        data-toggle="tooltip" title="Edit">
                        <i class="glyphicon glyphicon-pencil"></i>
                    </a>
                    <a href="delete_user.php?id=<?php echo (int)$a_user['id'];?>" class="btn btn-xs btn-danger"
                        data-toggle="tooltip" title="Remove">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>

    <?php include_once'add_user.php' ?>




</div>

<?php include_once'layouts/footer.php'; ?>


<script>
var properties = [
    'id',
    'name',
    'username',
    'userrole',
    'date',
    'grade',
    'section'
];

$.each(properties, function(i, val) {

    var orderClass = '';

    $("#" + val).click(function(e) {
        e.preventDefault();
        $('.filter__link.filter__link--active').not(this).removeClass('filter__link--active');
        $(this).toggleClass('filter__link--active');
        $('.filter__link').removeClass('asc desc');

        if (orderClass == 'desc' || orderClass == '') {
            $(this).addClass('asc');
            orderClass = 'asc';
        } else {
            $(this).addClass('desc');
            orderClass = 'desc';
        }

        var parent = $(this).closest('.header__item');
        var index = $(".header__item").index(parent);
        var $table = $('.table-content');
        var rows = $table.find('.table-row').get();
        var isSelected = $(this).hasClass('filter__link--active');
        var isNumber = $(this).hasClass('filter__link--number');

        rows.sort(function(a, b) {
            var x = $(a).find('.table-data').eq(index).text();
            var y = $(b).find('.table-data').eq(index).text();

            if (val === 'date') {
                // Parse dates for comparison
                x = new Date(x);
                y = new Date(y);
            }

            if (isNumber) {
                // Numeric sorting
                return isSelected ? x - y : y - x;
            } else {
                // String sorting
                if (isSelected) {
                    return x.localeCompare(y);
                } else {
                    return y.localeCompare(x);
                }
            }
        });

        $.each(rows, function(index, row) {
            $table.append(row);
        });

        return false;
    });

});
</script>