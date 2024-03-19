<?php

require "bd.php";
$bdd = getBD();


if(isset($_GET['email'])) {
    $email = $_GET['email'];

    // Préparation de la requête pour vérifier si l'e-mail existe déjà
    $stmt = $bdd->prepare("SELECT * FROM clients WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $emailExiste = $stmt->fetch();

    // Retourner une réponse en format JSON
    if($emailExiste) {
        echo json_encode(array("exists" => true));
    } else {
        echo json_encode(array("exists" => false));
    }
}
?>


