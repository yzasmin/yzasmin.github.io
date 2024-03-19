<?php
session_start();

// Vérifiez si la variable de session "panier" existe et si elle est un tableau
if (!isset($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
    $_SESSION['panier'] = array(); // Initialisez-la comme un tableau vide si elle n'existe pas
}

// Vider le panier si l'utilisateur a cliqué sur le bouton "Vider le panier"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vider_panier'])) {
    $_SESSION['panier'] = array(); // Réinitialiser le panier
    header('Location: panier.php'); // Recharger la page
    exit;
}

require "bd.php";
$bdd = getBD();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Panier</title>
</head>
<body>
    <div class="container">
        <h1>Votre Panier</h1>

        <?php
        // Vérifiez si le panier est vide
        if (empty($_SESSION['panier'])) {
            echo "<p>Votre panier ne contient aucun article.</p>";
        } else {
            // Début du tableau qui affiche les articles dans le panier
            echo '<table border="1">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix(€)</th>
                <th>Quantité</th>
            </tr>';
    
            $total = 0;
    
            // Parcourir le panier et afficher chaque article
            foreach ($_SESSION['panier'] as $article) {
                $id_art = $article['id_art'];
                $quantite = $article['quantite'];
    
                // Requête pour obtenir les détails de l'article depuis la base de données
                $query = $bdd->prepare('SELECT id_art, nom, prix FROM articles WHERE id_art = ?');
                $query->execute([$id_art]);
                $articleDetails = $query->fetch();
    
                // Afficher les détails de l'article dans le tableau
                echo '<tr>';
                echo '<td>' . htmlspecialchars($articleDetails['id_art']) . '</td>';
                echo '<td>' . htmlspecialchars($articleDetails['nom']) . '</td>';
                echo '<td>' . htmlspecialchars($articleDetails['prix']) . ' €</td>';
                echo '<td>' . htmlspecialchars($quantite) . '</td>';
                echo '</tr>';
    
                // Calculer le total
                $total += $articleDetails['prix'] * $quantite;
            }
            echo '</table>';
    
            // Afficher le total
            echo "<p>Total : $total €</p>";

            echo '<a href="commande.php">Passer la commande</a>';
        }
        ?>

        <!-- Formulaire pour vider le panier -->
        <form action="panier.php" method="post">
            <input type="submit" class='vider_button'name="vider_panier" value="Vider le panier" />
        </form>

        <!-- Lien pour retourner à la page d'accueil -->
        <a href="index.php" class="return-button">Retour à la page d'accueil</a>
</div>
</body>
</html>

