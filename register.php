<?php
session_start();
$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./register_css/register.css">
    <title>J.E Essential Shop | Register Now</title>
</head>
<body>

    <?php if (isset($_SESSION['register_error'])): ?>
    <p style="color:red;"><?= $_SESSION['register_error']; ?></p>
    <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

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



    <main class="main-register">
    <header class="register-heading">
        <h1>Welcomee to J.E Essentials Shop!</h1>
        <p>Please fill up the details below to register</p>
    </header>
    
  <form action="process_register.php" method="post">

    <label for="username">Username: </label>
    <?php if (isset($errors['username'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['username']); ?></p>
    <?php endif; ?>
    <input type="text" name="username" id="username"
           value="<?= htmlspecialchars($old['username'] ?? ''); ?>"
           placeholder="Enter username" required> <br>

    <div class="name-info">
        <label for="first_name">First Name: </label>
        <?php if (isset($errors['first_name'])): ?>
            <p style="color:red;"><?= htmlspecialchars($errors['first_name']); ?></p>
        <?php endif; ?>
        <input type="text" name="first_name" id="first_name"
               value="<?= htmlspecialchars($old['first_name'] ?? ''); ?>"
               placeholder="Enter first name" required> <br>

        <label for="middle_name">Middle Name: </label>
        <?php if (isset($errors['middle_name'])): ?>
            <p style="color:red;"><?= htmlspecialchars($errors['middle_name']); ?></p>
        <?php endif; ?>
        <input type="text" name="middle_name" id="middle_name"
               value="<?= htmlspecialchars($old['middle_name'] ?? ''); ?>"
               placeholder="Enter middle name"> <br>

        <label for="last_name">Last Name: </label>
        <?php if (isset($errors['last_name'])): ?>
            <p style="color:red;"><?= htmlspecialchars($errors['last_name']); ?></p>
        <?php endif; ?>
        <input type="text" name="last_name" id="last_name"
               value="<?= htmlspecialchars($old['last_name'] ?? ''); ?>"
               placeholder="Enter last name" required> <br>
    </div>

    <label for="email">Email: </label>
    <?php if (isset($errors['email'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['email']); ?></p>
    <?php endif; ?>
    <input type="email" name="email" id="email"
           value="<?= htmlspecialchars($old['email'] ?? ''); ?>"
           placeholder="Enter email" required> <br>

    <label for="address">Address: </label>
    <?php if (isset($errors['address'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['address']); ?></p>
    <?php endif; ?>
    <input type="text" name="address" id="address"
           value="<?= htmlspecialchars($old['address'] ?? ''); ?>"
           placeholder="Country, Province/State, City, Barangay, Street, Blk No./House No." required> <br>

    <div class="pass-info">
        <label for="password">Password: </label>
        <?php if (isset($errors['password'])): ?>
            <p style="color:red;"><?= htmlspecialchars($errors['password']); ?></p>
        <?php endif; ?>
        <input type="password" name="password" id="password" placeholder="Enter password" required> <br>

        <label for="confirm_password">Confirm Password: </label>
        <?php if (isset($errors['confirm_password'])): ?>
            <p style="color:red;"><?= htmlspecialchars($errors['confirm_password']); ?></p>
        <?php endif; ?>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required> <br>
    </div>

    <label for="phone">Phone: </label>
    <?php if (isset($errors['phone'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['phone']); ?></p>
    <?php endif; ?>
    <input type="tel" name="phone" id="phone"
           value="<?= htmlspecialchars($old['phone'] ?? ''); ?>"
           placeholder="Enter phone number" required> <br>

    <input type="submit" value="Register" name="register">

</form>
<div class="login-footer">
    <p>Already have an account? <a href="login.php">Log in here</a></p>
    </div>
  
</main>



<?php if (isset($_SESSION['register_success'])): ?>
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <h2>Account Successfully Created!</h2>
        <p>Welcome to J.E Essentials Shop. You can now log in to your account.</p>
        <div class="modal-actions">
            <a href="login.php" class="modal-btn modal-btn-primary">Go to Login</a>
            <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>
<?php unset($_SESSION['register_success']); ?>
<?php endif; ?>

<script src="javascript.js"></script>
</body>
</html>


