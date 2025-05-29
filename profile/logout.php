<?php 
session_start();
session_destroy();
session_start();
$_SESSION['success'] = "Logout succes.";
header('Location: index.php');
return;