<?php
session_start();
$login_error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../J.E Essentials/login_css/login.css">
    <title>Login</title>
</head>
<body>

<header class="site-nav">
    <div class="site-nav-inner">
        <a href="#top" class="logo">
          <img src="images/je_logo.svg" alt="">
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="#top">HOME</a></li>
                <li><a href="#about">ABOUT US</a></li>
                <li><a href="#featured">FEATURED</a></li>
                <li><a href="#app">OUR APP</a></li>
            </ul>
        </nav>
         
        <div class ="cta-nav">
    
        <a href="#contact" class="cta-btn-small">CONTACT US</a>
        <img src="images/arrow.svg" alt="">
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
             <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>




    <main class="main-login">
    <header class="login-heading">
        <h1>Welcome back to J.E Essentials Shop!</h1>
        <p>Please log in to continue</p>
    </header>

    <form action="process_login.php" method="post">

        <!-- Username or Email -->
        <label for="login_id">Username or Email: </label>
        <input type="text" name="login_id" id="login_id" placeholder="Enter username or email" required> <br>

        <!-- Password -->
        <label for="password">Password: </label>
        <input type="password" name="password" id="password" placeholder="Enter password" required> <br>

        <!-- Login Button -->
        <input type="submit" value="Login" name="login">

    </form>

    <div class="login-footer">
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    </main>
    <script src="javascript.js"></script>
</body>
</html>