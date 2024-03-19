<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>connexion</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="icon" href="Favicone.png">
</head>
<body>
<div class="container">
    <h1>Connexion</h1>
    <p>Vous n'avez pas encore de compte ? <a href="nouveau.php">Créez-en un ici</a>.</p>

    <form id="login-form" method="post" autocomplete="off">        
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="motdepasse">Mot de passe :</label>
        <input type="password" id="motdepasse" name="motdepasse" required><br><br>

        <input type="submit" value="Se connecter">
    </form>
    <div id="login-feedback"></div>
</div>
<script>
$(document).ready(function() {
    $("#login-form").submit(function(event) {
        event.preventDefault(); // Empêcher la soumission standard du formulaire

        var formData = $(this).serialize(); // Récupérer les données du formulaire

        $.ajax({
            url: 'connecter.php', // L'URL de votre script PHP pour la connexion
            type: 'post',
            data: formData,
            cache: false,
            success: function(response) {
                console.log("Réponse brute:", response);
                if (response.success) {
                    console.log("Redirection en cours...");
                    window.location.href = 'index.php';
                } else {
                    $("#login-feedback").text(response.message).css("color", "red");
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur AJAX :", xhr.responseText);
                $("#login-feedback").text("Erreur lors de la connexion : " + xhr.responseText).css("color", "red");
            }
        });
    });
});
</script>

</body>
</html>
