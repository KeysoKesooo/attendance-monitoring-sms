<?php
  ob_start();
  require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>

<div class="row">
    <div class="col-md-12">

    </div>
</div>

<div class="login-page">
    <div class="text-center">
        <h1>Welcome</h1>
        <p>Sign in to start your session</p>
        <h5> <?php echo display_msg($msg); ?></h5>
    </div>
    <form method="post" action="auth_v2.php" class="clearfix">
        <div class="form-group">
            <label for="username" class="control-label">Username</label>
            <input type="name" class="form-control" name="username" placeholder="Username">
        </div>
        <div class="form-group">
            <label for="Password" class="control-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="password">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-info  pull-right">Login</button>
        </div>
    </form>
</div>
<?php include_once('layouts/header.php'); ?>