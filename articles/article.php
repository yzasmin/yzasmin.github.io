<html lang=fr>
<?php
session_start();
?>
<?php
require "bd.php"; 
$id=$_GET['id_art'];
$bdd = getBD();
 $article=$bdd -> query('SELECT * FROM articles WHERE id_art='.$id)->fetch();
?>


<head>
<link rel="stylesheet" href="../css/styles.css" type="text/css" media="screen" />
<link rel="icon" href="Favicone.png">
<meta http-equiv="Content-Type"content="text/html; charset=UTF-8" />
<title>Article</title>
</head>
<body>
<div class="container">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Quantite</th>
            <th>Prix(€)</th>
            <th>Photo<th>
            </tr>
            <?php
    
            echo '<tr>';

            echo '<td>' . $article['id_art'] . '</td>';
            echo '<td>' . $article['nom'] . '</td>';
            echo '<td>'  .$article['quantite'] . '</td>';
            echo '<td>' . $article['prix'] . '</td>';
            echo '<td> <img src="' . $article['url_photo'] . '" alt="article" style="width: 40%; height: 40%;">'.'</td>';
            echo '<td>' . $article['description'] . '</td>';
        
            echo '</tr>';
        
            ?>
        </table>
    <?php    
    if (isset($_SESSION['client'])) {
    
        echo '<form action="ajouter.php" method="post">
            <h2>Ajouter au panier</h2>
            <label for="quantite">Quantité :</label>
            <input type="number" id="quantite" name="quantite" min="1" value="1">
            <input type="hidden" name="id_art" value="' . $article['id_art'] . '">
            <input type="submit" value="Ajouter au panier">
        </form>';
    }
    ?>
        
   
        <p><a href="../index.php" class="return-button">Retour à la page d'accueil</a></p>
</div>
</body>


<html>




