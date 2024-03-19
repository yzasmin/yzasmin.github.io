<?php
session_start();
require 'bd.php';
require 'analysemsg.php'; 

$bdd = getBD();
$user_id = $_SESSION['user_id']; 
$username = $_SESSION['username']; 
$message = $_POST['message'];

if (strlen($message) > 256) {
    echo json_encode(['success' => false, 'message' => 'Le message est trop long.']);
    exit;
}

$scoreMap = loadScoreMap('score_map.json');
$messageScore = evaluateMessage($message, $scoreMap);

if ($messageScore <= 0.5) {
    echo json_encode(['success' => false, 'message' => 'Message non autorisé.']);
    exit;
}

$stmt = $bdd->prepare("INSERT INTO messages (user_id, username, message) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $username, $message]);

echo json_encode(['success' => true]);
?>
