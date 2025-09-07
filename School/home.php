<?php
  require_once('includes/load.php');
  
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>
<?php $user = current_user(); ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="libs/css/main_dash.css" />
</head>

<style>
/* General Reset */
body,
html {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

/* Main Container */
.welcome-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 25px;
    min-height: 100vh;
    background: linear-gradient(135deg, #22177A, #1C325B);
    text-align: center;
    color: #fff;
}

/* Logo */
.welcome-logo {
    margin-bottom: 20px;
}

.logo {
    max-width: 300px;
    width: 100%;
    height: 300px;
}

/* Welcome Message */
.welcome-message {
    font-size: 2rem;
    margin: 10px 0 10px 0;
}

.welcome-description {
    font-size: 1rem;
    margin: 10px 0 100px;
    line-height: 1.5;
}

/* General Explore Button Styles */
.explore-btn {
    line-height: 1;
    text-decoration: none;
    display: inline-flex;
    border: none;
    cursor: pointer;
    align-items: center;
    gap: 0.75rem;
    background-color: var(--clr);
    color: #fff;
    border-radius: 10rem;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    padding-left: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: background-color 0.3s;
}

.explore-btn__icon-wrapper {
    flex-shrink: 0;
    width: 25px;
    height: 25px;
    position: relative;
    color: var(--clr);
    background-color: #fff;
    border-radius: 50%;
    display: grid;
    place-items: center;
    overflow: hidden;
}



.explore-btn:hover .explore-btn__icon-wrapper {
    color: #000;
}

.explore-btn__icon-svg--copy {
    position: absolute;
    transform: translate(-150%, 150%);
}

.explore-btn:hover .explore-btn__icon-svg:first-child {
    transition: transform 0.3s ease-in-out;
    transform: translate(150%, -150%);
}

.explore-btn:hover .explore-btn__icon-svg--copy {
    transition: transform 0.3s ease-in-out 0.1s;
    transform: translate(0);
}

/* Style for .explore-btn-container div */
.explore-btn-container {
    display: inline-block;
    margin: 20px 0;
    text-align: center;
    max-width: 300px;
    width: 100%;
}

/* Link Style */
.explore-btn-link {
    text-decoration: none;
}


/* Responsive Design */
@media (max-width: 768px) {
    .welcome-container {
        padding-top: 0px;
    }

    .welcome-message {
        font-size: 1.5rem;
        margin: 0px 0 0px 0;

    }

    .welcome-description {
        font-size: 0.9rem;
    }

    .welcome-btn {
        font-size: 0.9rem;
        padding: 8px 16px;
    }

}
</style>

<body>
    <div class="welcome-container">
        <div class="welcome-logo">
            <img src="/School/images/school_logo.png" alt="Logo" class="logo">
        </div>
        <h1 class="welcome-message">Welcome <?php echo $user['name']; ?></h1>
        <p class="welcome-description"> ACCESS DENIED</p>

        <div class="explore-btn-container">
            <a href="logout.php" class="explore-btn-link">
                <button class="explore-btn" style="--clr: #7808d0">
                    <span class="explore-btn__icon-wrapper">
                        <svg viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="explore-btn__icon-svg" width="10">
                            <path
                                d="M13.376 11.552l-.264-10.44-10.44-.24.024 2.28 6.96-.048L.2 12.56l1.488 1.488 9.432-9.432-.048 6.912 2.304.024z"
                                fill="currentColor"></path>
                        </svg>

                        <svg viewBox="0 0 14 15" fill="none" width="10" xmlns="http://www.w3.org/2000/svg"
                            class="explore-btn__icon-svg explore-btn__icon-svg--copy">
                            <path
                                d="M13.376 11.552l-.264-10.44-10.44-.24.024 2.28 6.96-.048L.2 12.56l1.488 1.488 9.432-9.432-.048 6.912 2.304.024z"
                                fill="currentColor"></path>
                        </svg>
                    </span>
                    Back to login
                </button>
            </a>
        </div>
    </div>
</body>

</html>