<?php
session_start();
include "bd.php";

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php'); // Redirigez les utilisateurs non connectés
    exit;
}

$bdd = getBD();
$id_client = $_SESSION['client']['id_client'];

$reponse = $bdd->prepare("SELECT Commandes.*, Articles.nom AS article_nom, Articles.prix AS article_prix 
                           FROM Commandes 
                           JOIN Articles ON Commandes.id_art = Articles.id_art
                           WHERE id_client = ?");
$reponse->execute([$id_client]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Commandes</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">
</head>
<body>
<div class="container">
    <h1>Historique des Commandes</h1>
    <table border="1">
        <tr>
            <th>ID Commande</th>
            <th>ID Article</th>
            <th>Nom Article</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>État</th>
        </tr>
        <?php
            while ($donnees = $reponse->fetch()) {
                echo '<tr>';
                echo '<td>' . $donnees['id_commande'] . '</td>';
                echo '<td>' . $donnees['id_art'] . '</td>';
                echo '<td>' . $donnees['article_nom'] . '</td>';
                echo '<td>' . $donnees['article_prix'] . '</td>';
                echo '<td>' . $donnees['quantite'] . '</td>';
                echo '<td>' . ($donnees['envoi'] ? 'Envoyée' : 'En attente') . '</td>';
                echo '</tr>';
            }
            $reponse->closeCursor();
        ?>
    </table>
</div>
</body>
</html>
