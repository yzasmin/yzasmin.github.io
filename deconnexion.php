<?php
session_start(); // Démarrez la session
session_destroy(); // Détruisez la session
header("Location: index.php"); // Redirigez l'utilisateur vers "index.php"
exit; // Assurez-vous de quitter le script après la redirection
?>