<?php
session_start();
session_destroy();          
header("Location: auth.php?mode=signin&logout=1");
exit();
?>
