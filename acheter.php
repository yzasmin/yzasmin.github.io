<?php
session_start();
require "bd.php";
$bdd = getBD();

require_once('vendor/autoload.php');
require_once('stripe.php'); 

$line_items = [];

foreach ($_SESSION['panier'] as $article) {
    $id_art = $article['id_art'];
    $quantite = $article['quantite'];

    $stmt = $bdd->prepare('SELECT * FROM articles WHERE id_art = ?');
    $stmt->execute([$id_art]);
    $ligne = $stmt->fetch();

    if ($ligne) {
        $prix = $ligne['prix'];
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $ligne['nom']],
                'unit_amount' => $prix * 100,
            ],
            'quantity' => $quantite,
        ];
    }
}

$checkout_session = $stripe->checkout->sessions->create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'success_url' => 'http://localhost/Saoudyasmina/success.php',
    'cancel_url' => 'http://localhost/Saoudyasmina/cancel.php',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
exit;
