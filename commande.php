<?php
session_start();
require "bd.php";
require_once('vendor/autoload.php');
require_once('stripe.php');

// Assurez-vous que le panier existe et qu'il contient des articles
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

$bdd = getBD();
$nom_client = $_SESSION['client']['nom'];
$prenom_client = $_SESSION['client']['prenom'];
$adresse_client = $_SESSION['client']['adresse'];

$line_items = [];
$total = 0;

foreach ($_SESSION['panier'] as $article) {
    $id_art = $article['id_art'];
    $quantite = $article['quantite'];

    $stmt = $bdd->prepare('SELECT * FROM articles WHERE id_art = ?');
    $stmt->execute([$id_art]);
    $ligne = $stmt->fetch();

    if ($ligne) {
        $prix = $ligne['prix'];

        // Ajouter l'article à Stripe line items
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $ligne['nom']],
                'unit_amount' => $prix * 100, // Stripe utilise les centimes
            ],
            'quantity' => $quantite,
        ];

        $total += $prix * $quantite;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif de la Commande</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">
</head>
<body>
    <div class="container">
        <h1>Récapitulatif de votre commande :</h1>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix(€)</th>
                <th>Quantité</th>
            </tr>
            <?php
            $total = 0;
            foreach ($_SESSION['panier'] as $article) {
                $id_art = $article['id_art']; // Assurez-vous que 'id_art' est correctement défini dans la session
                $quantite = $article['quantite'];

                $stmt = $bdd->prepare('SELECT * FROM articles WHERE id_art = ?');
                $stmt->execute([$id_art]);
                $ligne = $stmt->fetch();

                if ($ligne) {
                    $prix = $ligne['prix'];

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($ligne['id_art']) . '</td>';
                    echo '<td>' . htmlspecialchars($ligne['nom']) . '</td>';
                    echo '<td>' . htmlspecialchars($prix) . ' €</td>';
                    echo '<td>' . htmlspecialchars($quantite) . '</td>';
                    echo '</tr>';

                    $total += $prix * $quantite;
                }
            }
            ?>
        </table>
        <p>Montant de votre commande : <?php echo $total; ?> €</p>
        <p>La commande sera expédiée à l’adresse suivante :</p>
        <p><?php echo htmlspecialchars($nom_client) . ' ' . htmlspecialchars($prenom_client); ?></p>
        <p><?php echo htmlspecialchars($adresse_client); ?></p>

        <form action="acheter.php" method="post">
            <input type="submit" value="Valider" />
        </form>
        <a href="index.php">Retour à la page d'accueil</a>
    </div>
</body>
</html>
