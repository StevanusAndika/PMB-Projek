<?php
session_start(); // Mulai session
session_destroy(); // Menghancurkan session
header("Location: ../index.php"); // Redirect ke index.php yang berada di luar folder PMB-Projek
exit();
?>
