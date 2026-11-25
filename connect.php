<?php

$S_Name   = "127.0.0.1";   
$U_Name   = "root";
$Password = "";            
$Db_name  = "autoparts_db";

$con = new mysqli($S_Name, $U_Name, $Password, $Db_name);


if ($con->connect_error) {
    die("<p style='color:red;padding:20px;'>
         ❌ Connexion échouée : " . $con->connect_error . "<br>
         Vérifiez que MySQL est démarré dans XAMPP.
         </p>");
}

$con->set_charset("utf8");
?>
