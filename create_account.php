<?php
session_start(); // Assurez-vous que la session est démarrée
require 'bd.php';
require_once 'vendor/autoload.php';
require 'stripe.php';
$bdd = getBD();

// Vérification du jeton CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité.']);
    exit;
}


// Récupération et validation des données
$nom = filter_input(INPUT_POST, 'n', FILTER_SANITIZE_STRING);
$prenom = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
$adresse = filter_input(INPUT_POST, 'adr', FILTER_SANITIZE_STRING);
$telephone = filter_input(INPUT_POST, 'num', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'mdp1', FILTER_SANITIZE_STRING);

if (!$email || !$password) {
    // Données invalides
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Hashage du mot de passe
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insertion dans la base de données
try {
    $stripeCustomer = $stripe->customers->create(['email' => $email]);
    $stripeCustomerId = $stripeCustomer->id;

    $stmt = $bdd->prepare("INSERT INTO clients (ID_STRIPE,nom, prenom, adresse, telephone, email, mdp) VALUES (?, ?, ?, ?,?,?,?)");
    $compteCreeAvecSucces = $stmt->execute([$stripeCustomerId,$nom, $prenom, $adresse,$telephone, $email, $hashedPassword]);

    if ($compteCreeAvecSucces) {
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès. Vous allez être redirigé.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur Stripe : ' . $e->getMessage()]);
}

?>
