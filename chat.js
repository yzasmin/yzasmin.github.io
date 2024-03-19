document.getElementById('sendMessage').addEventListener('click', sendMessage);

function sendMessage() {
    var message = document.getElementById('messageInput').value;
    if (message.trim() === '') {
        return; // Ne rien faire si le message est vide
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'sendMessage.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    document.getElementById('messageInput').value = ''; // Vider le champ de saisie
                    fetchMessages(); // Mise à jour immédiate des messages
                } else {
                    alert(response.message); // Afficher une alerte si le message est non autorisé
                }
            } catch (e) {
                console.error("Erreur lors de l'analyse de la réponse JSON: ", e);
                console.log("Réponse brute du serveur: ", xhr.responseText);
            }
        } else {
            console.error("Erreur lors de la réponse du serveur: ", xhr.status, xhr.statusText);
        }
    };
    xhr.send('message=' + encodeURIComponent(message));
}

function fetchMessages() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'getMessages.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var messages = JSON.parse(xhr.responseText);
            var display = messages.reverse().map(function(msg) {
                return '<strong>' + msg.username + ' dit</strong>: ' + msg.message;
            }).join('<br>');
            document.getElementById('messages').innerHTML = display;
            document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
        }
    };
    xhr.send();
}

// Récupérer les messages toutes les 2 secondes
setInterval(fetchMessages, 2000);
