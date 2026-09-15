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
    <link rel="stylesheet" href="../J.E Essentials/style_css/style.css">
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
                <li><a href="index.php#top">HOME</a></li>
                <li><a href="index.php#about">ABOUT US</a></li>
                <li><a href="index.php#featured">FEATURED</a></li>
                <li><a href="index.php#app">OUR APP</a></li>
                <li><a href="#contact">CONTACT US</a></li>
                <li class="mobile-shop"><a class="mobile-shop" href="shop.php">SHOP NOW</a></li>
            </ul>
        </nav>

    
         
        <div class ="cta-nav">
    
        <a href="shop.php" class="cta-btn-small">SHOP NOW</a>
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
        <h1>Welcome to J.E Essentials Shop!</h1>
        <p>Please log in to continue</p>
    </header>
             
              <?php if ($login_error): ?>
    <div class="login-error">
        <?= htmlspecialchars($login_error) ?>
    </div>
<?php endif; ?>


    <form action="process_login.php" method="post">

        <!-- Username or Email -->
        <label for="login_id">Username or Email: </label>
        <input type="text" name="login_id" id="login_id" placeholder="Enter username or email" required> <br>

        <!-- Password -->
        <div class="password-field">
    <input type="password" name="password" id="password" placeholder="Enter password" required>
    <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
        <svg class="eye-icon" id="eyeIcon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
        </svg>
    </button>
</div>
          
        <div class="login-options">

    <label class="login-checkbox">
        <input type="checkbox" name="remember">
        <span>Keep me signed in</span>
    </label>
</div>

        <!-- Login Button -->
        <input type="submit" value="Login" name="login">

    </form>

    <div class="login-footer">
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    </main>
    <script>
        const passwordInput = document.getElementById('password');
const passwordToggle = document.getElementById('passwordToggle');
const eyeIcon = document.getElementById('eyeIcon');

if (passwordInput && passwordToggle && eyeIcon) {
    passwordToggle.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordToggle.setAttribute('aria-label', 'Hide password');
            eyeIcon.innerHTML = `
                <path d="M3 3L21 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M10.5 5.2C11 5.1 11.5 5 12 5C18.5 5 22 12 22 12C22 12 20.7 14.6 18.4 16.7M6.2 6.2C3.5 8.3 2 12 2 12C2 12 5.5 19 12 19C13.6 19 15.1 18.6 16.4 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.9 9.9C9.4 10.4 9.1 11.2 9.1 12C9.1 13.6 10.4 14.9 12 14.9C12.8 14.9 13.6 14.6 14.1 14.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            `;
        } else {
            passwordInput.type = 'password';
            passwordToggle.setAttribute('aria-label', 'Show password');
            eyeIcon.innerHTML = `
                <path d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
            `;
        }
    });
}
    </script>
    <script src="javascript.js"></script>
</body>
</html>