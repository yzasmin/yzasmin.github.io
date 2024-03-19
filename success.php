<?php
session_start();
require "bd.php";
$bdd = getBD();

if (!empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $article) {
        $id_art = $article['id_art'];
        $id_client = $_SESSION['client']['id_client'];
        $quantite = $article['quantite'];

        // Insérer dans la table Commandes
        $stmt = $bdd->prepare("INSERT INTO Commandes (id_art, id_client, quantite, envoi) VALUES (?, ?, ?, false)");
        $stmt->execute([$id_art, $id_client, $quantite]);

        // Mettre à jour les stocks
        $stmt = $bdd->prepare("UPDATE articles SET quantite = quantite - ? WHERE id_art = ?");
        $stmt->execute([$quantite, $id_art]);
    }
    // Vider le panier
    unset($_SESSION['panier']);
}
?>

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement Réussi</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">
</head>
<body>
<div class="container">
    <h1>Paiement Réussi</h1>
    <p>Votre paiement a été effectué avec succès. Merci pour votre commande !</p>
    <p><a href="index.php">Retour à la page d'accueil</a></p>

</div>
</body>
</html>


