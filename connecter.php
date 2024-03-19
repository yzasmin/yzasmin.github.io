<?php
require "bd.php";
session_start();

header('Content-Type: application/json'); // Indique que la réponse sera en JSON

if (empty($_POST["email"]) || empty($_POST["motdepasse"])) {
    echo json_encode(["success" => false, "message" => "Email ou mot de passe manquant."]);
    exit;
}

$email = $_POST["email"];
$motdepasse = $_POST["motdepasse"];

$bdd = getBD();

// Modifiez la requête pour récupérer uniquement le hash du mot de passe
$sql = "SELECT id_client, prenom, nom, ID_STRIPE, mdp, adresse FROM clients WHERE email = :email";
$stmt = $bdd->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();


$clients = $stmt->fetch();

// Utilisez password_verify pour comparer les mots de passe
if ($clients && password_verify($motdepasse, $clients['mdp'])) {
    $_SESSION['client'] = $clients; // Stocke les informations du client dans la variable de session
    $_SESSION['ID_STRIPE'] = $clients['ID_STRIPE']; // Stocke l'ID Stripe dans la session
    $_SESSION['user_id'] = $clients['id_client'];
    $_SESSION['username'] = $clients['prenom'];

    echo json_encode(["success" => true, "message" => "Connexion réussie.", "ID_STRIPE" => $clients['ID_STRIPE']]);
} else {
    echo json_encode(["success" => false, "message" => "Identifiants incorrects."]);
}
?>
