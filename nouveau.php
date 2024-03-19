<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau Client</title>
    <link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />
    <link rel="icon" href="Favicone.png">

    <!-- Inclusion de jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="container">
    <h1>Nouveau Client</h1>

    <form action="enregistrement.php" method="get" id="form">
        Nom: <input type="text" name="n" id="n" value=""><br>
        <span id="error-n" class="error-message"></span><br>

        Prénom: <input type="text" name="p" id="p" value=""><br>
        <span id="error-p" class="error-message"></span><br>

        Adresse: <input type="text" name="adr" id="adr" value=""><br>
        <span id="error-adr" class="error-message"></span><br>

        Numéro de téléphone: <input type="text" name="num" id="num" value=""><br>
        <span id="error-num" class="error-message"></span><br>

        Adresse e-mail: <input type="text" name="mail" id="mail" value=""><br>
        <span id="error-mail" class="error-message"></span><br>

        Mot de passe: <input type="password" name="mdp1" id="mdp1"><br>
        <span id="error-mdp1" class="error-message"></span><br>

        Confirmer votre mot de passe: <input type="password" name="mdp2" id="mdp2"><br>
        <span id="error-mdp2" class="error-message"></span><br>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">


        <input type="submit" value="Soumettre" disabled>
    </form>
    <div id="form-feedback"></div>


    <a href="index.php">Retour à la page d'accueil</a>
</div>

<script>
    $(document).ready(function() {
        function validateEmail(email) {
            var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            return re.test(email);
        }

        function validatePassword(pw) {
            var re = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
            return re.test(pw);
        }

        function updateSubmitButtonState() {
            var isValid = true;

            if ($("#n").val().trim() === "" || 
                $("#p").val().trim() === "" ||
                $("#adr").val().trim() === "" ||
                $("#num").val().trim() === "" ||
                !validateEmail($("#mail").val()) || 
                !validatePassword($("#mdp1").val()) ||
                $("#mdp2").val() !== $("#mdp1").val()) {
                isValid = false;
            }

            $("input[type=submit]").prop("disabled", !isValid);
        }
        function checkEmptyFields() {
            $("input[type=text], input[type=password]").each(function() {
                var fieldId = $(this).attr('id');
                var value = $(this).val().trim();
                var errorId = "error-" + fieldId;

            if (value === "") {
                $("#" + fieldId).css("background-color", "red");
                $("#" + errorId).text("Ce champ ne peut pas être vide.");
            } else {
                $("#" + fieldId).css("background-color", ""); // Ou une autre couleur de fond si nécessaire
                $("#" + errorId).text("");
            }
        });

        updateSubmitButtonState(); // Mettre à jour l'état du bouton de soumission
    }

// Appliquer la vérification à chaque modification des champs
$("input[type=text], input[type=password]").on("input", function() {
    checkEmptyFields();
});

        $("input").on("input", function() {
            var fieldId = $(this).attr('id');
            var value = $(this).val().trim();

            if (fieldId === 'mdp1') {
                if (!validatePassword(value)) {
                    $(this).css("background-color", "red");
                    $("#error-mdp1").text("Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.");
                } else {
                    $(this).css("background-color", "green");
                    $("#error-mdp1").text("");
                }
            }

            if (fieldId === 'mdp2') {
                if (value !== $("#mdp1").val()) {
                    $(this).css("background-color", "red");
                    $("#error-mdp2").text("Les mots de passe ne correspondent pas.");
                } else {
                    $(this).css("background-color", "green");
                    $("#error-mdp2").text("");
                }
            }

            updateSubmitButtonState();
        });

        // Vérification de l'e-mail via AJAX
        $("#mail").on("input", function() {
            var email = $(this).val();
            if(email.trim() !== "") {
                $.get("check_email.php", { email: email }, function(data) {
                    var response = JSON.parse(data);
                    if(response.exists) {
                        $("#mail").css("background-color", "red");
                        $("#error-mail").text("L'adresse e-mail est déjà utilisée.");
                    } else {
                        $("#mail").css("background-color", "green");
                        $("#error-mail").text("");
                    }
                    // Mise à jour de l'état du bouton de soumission
                    updateSubmitButtonState();
                });
            }
        });
    });
    $("#form").submit(function(event) {
    event.preventDefault(); // Empêcher la soumission standard du formulaire

    var formData = $(this).serialize(); // Récupérer les données du formulaire

    $.ajax({
        url: 'create_account.php', // URL de votre script PHP pour créer le compte
        type: 'post',
        data: formData,
        success: function(response) {
            var res = JSON.parse(response);
            if(res.success) {
                $("#form-feedback").text(res.message).css("color", "green");
                setTimeout(function() {
                    window.location.href = 'index.php'; // Rediriger vers l'accueil
                }, 1000);
            } else {
                $("#form-feedback").text(res.message).css("color", "red");
            }
        },
        error: function() {
            $("#form-feedback").text("Une erreur est survenue lors de la création du compte.").css("color", "red");
        }
    });
});

</script>

</body>
</html>
