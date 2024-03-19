<!DOCTYPE html>
<?php
session_start();
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Boutique du Savon</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">
</head>
<body>
<div class="container">
<?php
        if (isset($_SESSION['client'])) {
            // L'utilisateur est connecté, affichez un lien vers la page du panier
            echo '<a href="panier.php"class="panier-button" >Voir le panier</a>';
            // Affichez les liens vers le panier et l'historique des commandes
            echo '<a href="historique.php" class="history-button">Historique des commandes</a><br>';
        } else {
            // L'utilisateur n'est pas connecté, affichez d'autres liens ou contenu
            echo '<a href="nouveau.php" class="new-client-button"> Nouveau Client </a> <br>';
            echo '<a href="connexion.php" class="button"> Se connecter </a> <br>';
        }
        ?><br>
    
        <?php
        if (isset($_SESSION['client'])) {
            echo '<a href="deconnexion.php" class="button">Se déconnecter</a> <br>';
        } 
    else{
        echo "";
    }
    ?>
    <?php
        if (isset($_SESSION['client'])) {
        $prenom = $_SESSION['client']['prenom'];
        $nom = $_SESSION['client']['nom'];
        echo "<p class='welcome-user'>Bonjour $prenom  ! </p>";
        } 
    else {
        echo '<p class="welcome-text">Bienvenue sur notre site !</p>';
    }
    ?>

    <?php
    include "bd.php";

    $host = 'localhost';
    $dbname = 'laboutiquedusavon';
    $username = 'root';
    $password = '';

    $bdd = getBD();
    $reponse = $bdd->query('SELECT * FROM Articles');
    ?>

    <h1>Retrouvez tous les articles de la Boutique !</h1>
    <br />
    <br />

    <table border="1">
    <table border="1">
        <tr>
        <th>Id</th>
        <th>Nom</th>
        <th>Qt_en_stock</th>
        <th>Prix</th>
        <th>Description</th>
        </tr>
        <?php
    while ($donnees = $reponse->fetch()) {
        echo '<tr>';
        echo '<td>' . $donnees['id_art'] . '</td>';
        echo '<td>  <a href="articles/article.php?id_art=' . $donnees['id_art'] .'">'.$donnees['nom'] . '</td>';
        echo '<td>' . $donnees['quantite'] . '</td>';
        echo '<td>' . $donnees['prix'] . '</td>';
        echo '<td>' . $donnees['description'] . '</td>';
        
        echo '</tr>';
        }
        $reponse->closeCursor();
        ?>
    </table>


    <img id="toggleChatButton" src="chatt.png" alt="Icône de chat">

    <div id="chatBox" style="display: none; position: fixed; bottom: 20px; right: 0; width: 300px; height: 400px; overflow: auto; border: 1px solid #ccc;">
    <div id="messages"></div>
    <div id="messageInputContainer">
        <input type="text" id="messageInput" maxlength="256">
        <button id="sendMessage">Envoyer</button>
    </div>
</div>

    <script src="chat.js"></script>
    <script>
    document.getElementById('toggleChatButton').addEventListener('click', function() {
        var chatBox = document.getElementById('chatBox');
        if (chatBox.style.display === "none") {
        chatBox.style.display = "block";
        } else {
            chatBox.style.display = "none";
        }
    });
    </script>



    <footer>
        <a href="contact/contact.html">Contact</a>
    </footer>
</div>
</body>
</html>
