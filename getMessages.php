<?php
session_start();
require 'bd.php';

$bdd = getBD();

// En-tête pour indiquer une réponse JSON
header('Content-Type: application/json');

try {
    // Supprimer les messages de plus de 10 minutes
    $bdd->query("DELETE FROM messages WHERE timestamp < (NOW() - INTERVAL 10 MINUTE)");

    // Récupérer les messages
    $stmt = $bdd->prepare("SELECT username, message FROM messages ORDER BY timestamp DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
} catch (PDOException $e) {
    // En cas d'erreur, renvoyer un message d'erreur en JSON
    echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
