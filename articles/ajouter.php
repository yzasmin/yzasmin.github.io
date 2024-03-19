<?php
session_start();
require "bd.php";
$bdd = getBD();

$id_art = $_POST['id_art'];
$quantiteDemandee = $_POST['quantite'];

// Requête pour obtenir la quantité en stock
$queryStock = $bdd->prepare('SELECT quantite FROM articles WHERE id_art = ?');
$queryStock->execute([$id_art]);
$articleStock = $queryStock->fetch();

$stockDisponible = $articleStock['quantite'];

// Vérifier si l'article est déjà dans le panier
$articleTrouve = false;
foreach ($_SESSION['panier'] as $index => $article) {
    if ($article['id_art'] == $id_art) {
        $nouvelleQuantite = min($article['quantite'] + $quantiteDemandee, $stockDisponible);
        $_SESSION['panier'][$index]['quantite'] = $nouvelleQuantite;
        $articleTrouve = true;
        break;
    }
}

// Si l'article n'est pas dans le panier, ajoutez-le
if (!$articleTrouve) {
    $quantiteAjouter = min($quantiteDemandee, $stockDisponible);
    $article = array(
        'id_art' => $id_art,
        'quantite' => $quantiteAjouter
    );
    $_SESSION['panier'][] = $article;
}

// Redirection
header("Location: ../index.php");
exit;
?>
