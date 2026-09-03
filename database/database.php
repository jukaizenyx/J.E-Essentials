<?php
    
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "je_essentials";

    //make a connection variable

 
    try {
    $conn =  mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    }
        catch (mysqli_sql_exception) {   
               echo "Couldn't connect";
           }
        
?>