<?php

include("database/database.php");

$username = $_POST["username"];
$password = $_POST["confirm_password"];
$email = $_POST["email"];
$first_name = $_POST["first_name"];
$last_name = $_POST["last_name"];
$middle_name = $_POST["middle_name"];
$address = $_POST["address"];
$phone = $_POST["phone"];


$sql_user_info = "INSERT INTO users (`username`, `first_name`, `middle_name`, `last_name`, `email`, `address`, `password`, `phone`)
                   VALUES('$username','$first_name', '$middle_name', '$last_name', '$email' , '$address' , '$password', '$phone')";

try {
    mysqli_query($conn, $sql_user_info);
}
catch (mysqli_sql_exception ) {
    echo "You couldn't be registered. Please try again.";
}


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../J.E Essentials/register_css/register.css">
    <title>Document</title>
</head>
<body>
    <main class="main-register">
    <header class="register-heading">
        <h1>Welcomee to J.E Essentials Shop!</h1>
        <p>Please fill up the details below to register</p>
    </header>
    
  <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?> </form>" method="post">

    <!-- Username -->
    <label for="username">Username: </label>
    <input type="text" name="username" id="username" placeholder="Enter username" required> <br>

 <div class="name-info">
 
    <!-- First Name -->
    <label for="first_name">First Name: </label>
    <input type="text" name="first_name" id="first_name" placeholder="Enter first name" required> <br>

    <!-- Middle Name -->
    <label for="middle_name">Middle Name: </label>
    <input type="text" name="middle_name" id="middle_name" placeholder="Enter middle name"> <br>

    <!-- Last Name -->
    <label for="last_name">Last Name: </label>
    <input type="text" name="last_name" id="last_name" placeholder="Enter last name" required> <br>
</div>

    <!-- Email -->
    <label for="email">Email: </label>
    <input type="email" name="email" id="email" placeholder="Enter email" required> <br>

    <!-- Address -->
    <label for="address">Address: </label>
    <input type="text" name="address" id="address" placeholder="Country, Province/State, City, Barangay, Street, Blk No./House No." required> <br>

 <div class="pass-info">
    <!-- Password -->
    <label for="password">Password: </label>
    <input type="password" name="password" id="password" placeholder="Enter password" required> <br>

    <!-- Confirm Password -->
    <label for="password">Confirm Password: </label>
    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required> <br>
</div>
    <!-- Phone -->
    <label for="phone">Phone: </label>
    <input type="tel" name="phone" id="phone" placeholder="Enter phone number" required> <br>

    <!-- Register Button -->
    <input type="submit" value="Register">

</form>
  
</main>
</body>
</html>


