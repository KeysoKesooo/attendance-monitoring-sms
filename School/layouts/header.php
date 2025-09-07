<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>

        <?php 
        if (!empty($page_title)) {
            echo remove_junk($page_title);
        } elseif (!empty($user) && isset($user['name'])) {
            echo ucfirst($user['name']);
        } else {
            echo "TRECE MARTIRES CITY SENIOR HIGH SCHOOL";
        }
        ?>
    </title>
    <link rel="icon" size:"16x16" href="/TMCSHS/images/fav_icon.ico1" type="image/ico">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css">
    <link rel="stylesheet" href="libs/css/main_dash.css">
    <link rel="stylesheet" href="libs/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>


    <?php if ($session->isUserLoggedIn(true)): ?>
    <header id="header">
        <div class="header-content">
            <div class="header-name pull-left" style="font-size: 20px;">
                <strong><?php echo display_page_title(); ?></strong>

            </div>

            <div class="pull-right clearfix">
                <ul class="info-menu list-inline list-unstyled">
                    <li class="profile">
                        <a href="#" data-toggle="dropdown" class="toggle" aria-expanded="false">
                            <img src="uploads/users/<?php echo $user['image'];?>" alt="" class="img-circle img-inline">
                            <span><?php echo remove_junk(ucfirst($user['name'])); ?></span>
                        </a>
                        <!-- Dropdown Menu -->
                        <ul class="dropdown-menu">
                            <li>
                                <a href="edit_account.php" title="edit account">
                                    <i class="glyphicon glyphicon-cog"></i>
                                    Settings
                                </a>
                            </li>
                            <li class="last">
                                <a href="logout.php">
                                    <i class="glyphicon glyphicon-log-out"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>


    <div class="sidebar">
        <?php $user = current_user(); 
// Make sure user array and user_level are set
if (isset($user) && isset($user['user_level'])): 
    switch ((int)$user['user_level']) {
        case 1:
            // Admin menu
            include_once('admin_menu.php');
            break;
        case 2:
            // Special user
            include_once('special_menu.php');
            break;
        case 3:
            // Normal users
            include_once('user_menu.php');
            break;
        case 4:
            // Normal users
            include_once('student_menu.php');
            break;
        default:
            echo "<p>Unknown user level.</p>";
            break;
    }
else: ?>
        <p>User level not set or invalid.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php 
    
    $user = current_user(); 
// Debugging mobile-specific issue
if (isset($user['user_level'])) {
    echo 'User level: ' . $user['user_level'];  // Debugging output
} else {
    echo 'User level not set';  // Debugging message
}
?>

    <?php if ($session->isUserLoggedIn(true)): ?>
    <div class="mob-nav">
        <?php 
// level 1 = admin
// level 2 = faculty
// level 3 = parent
if (isset($user['user_level'])): ?>
        <?php if ($user['user_level'] === '1'): ?>
        <!-- Admin menu -->
        <?php include_once('mobadmin_menu.php'); ?>
        <?php elseif ($user['user_level'] === '2'): ?>
        <!-- Faculty menu -->
        <?php include_once('mobfaculty_menu.php'); ?>
        <?php elseif ($user['user_level'] === '3'): ?>
        <!-- Parent and Level 4 User menu -->
        <?php include_once('mobuser_menu.php'); ?>
        <?php elseif ($user['user_level'] === '4'): ?>
        <!-- Parent and Level 4 User menu -->
        <?php include_once('mobstudent_menu.php'); ?>
        <?php else: ?>
        <p>User level invalid: <?php echo $user['user_level']; ?></p>
        <?php endif; ?>
        <?php else: ?>
        <p>User level not set or user data incomplete.</p>
        <?php endif; ?>

    </div>
    <?php endif; ?>



    <div class="page">
        <div class="container-fluid">