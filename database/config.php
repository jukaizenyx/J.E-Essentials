//a php showing the

<?php
    
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "je_essentials";

    //make a connection variable

 
    
    $conn =  mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    
    if (!$conn) {
    die("Couldn't connect: " . mysqli_connect_error());
}


?>