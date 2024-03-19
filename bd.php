<?php
function getBD(){
$host = 'localhost';
$dbname = 'laboutiquedusavon';
$username = 'root';
$password = '';
$bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
return $bdd;
}
?>