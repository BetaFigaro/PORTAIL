<!DOCTYPE html>
<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : pageuser.php
// Cette page est l'interface principale pour les utilisateurs "normaux" connectés.
// Elle vérifie que l'utilisateur est bien authentifié, gère la déconnexion, l'inactivité,
// et permet l'envoi de commandes MQTT pour ouvrir le portail.

session_start(); // Démarrage de la session utilisateur
require_once 'utils.php'; // Inclusion de fonctions comme la BDD ou le logging

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: index.php'); // Redirection vers login si non connecté
    exit;
}

// Gère l'inactivité : déconnexion automatique après 15 minutes
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Déconnexion manuelle via le formulaire
if (isset($_POST['logout'])) {
    session_unset();     // Supprime toutes les variables de session
    session_destroy();   // Détruit la session
    header('Location: index.php'); // Retour à la connexion
    exit;
}

// Mise à jour de l'heure de dernière activité à chaque action
$_SESSION['last_activity'] = time();
?>
<html>
<head>
    <title>Gestion du Portail</title>
    <meta charset="UTF-8"> <!-- Support des accents -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> <!-- Feuille de style -->
</head>
<body>
    
    <!-- En-tête -->
    <div class="center-content">
        <font face="arial" size="7" color="WHITE">
        <b>GESTION DU PORTAIL</b>
        </font>
    </div>
    
    <!-- Bouton pour ouvrir le portail -->
    <div class="center-content">
        <font face="arial" size="6" color="WHITE">
        <br> 
        <?php
        // Génère un bouton qui appelle la fonction JS de publication
        echo '<button class="bouton" onclick="publication_OUVERTURE()">OUVERTURE DU PORTAIL</button>';
        ?>
        <!-- Affichage de l état du portail récupéré via MQTT -->
        <p id="etat" style="color: white;"><br> Etat : FERMER</p>
        </font>
    </div>
    
    <!-- Formulaire pour se déconnecter -->
    <form method="post" action="">
        <br>
        <button type="submit" name="logout" class="bouton-deco" style="background-color: #f44336;">Déconnexion</button> 
    </form>

    <!-- Intégration de la bibliothèque MQTT -->
    <script>
    const CLE = "<?= $_SESSION['cle'] ?? '' ?>";
    </script>
    <script src="/JS/mqtt.min.js"></script>
    <script type="text/javascript">
        // Vérifie si mqtt est chargé
        if (typeof mqtt === 'undefined') {
            console.error("La bibliothèque mqtt.min.js n'est pas chargée !");
        }

        // Configuration de la connexion au broker MQTT
        const connectUrl = 'wss://mqtt.projet.betacorps.ovh'; // Adresse du broker (via WebSocket sécurisé)
        const clientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8); // ID client aléatoire

        const options = {
            keepalive: 60,
            clientId: clientId,
            protocolId: 'MQTT',
            protocolVersion: 4,
            clean: true,
            reconnectPeriod: 1000,
            connectTimeout: 4000,
            will: {
                topic: 'WillMsg',
                payload: 'Connection Closed abnormally..!',
                qos: 0,
                retain: false
            },
            username: 'portail-read',
            password: '8AZDhOoUfQdz'
        };

        // Connexion au broker avec les options définies
        const client = mqtt.connect(connectUrl, options);

        // Reconnexion automatique en cas de coupure
        client.on('reconnect', (error) => {
            console.log('Reconnexion en cours :', error);
        });

        // Affiche une erreur si la connexion échoue
        client.on('error', (error) => {
            console.error('Échec de la connexion :', error);
        });

        // Quand la connexion est établie
        const topicEtat = `portail/${CLE}/action`;

        client.subscribe(topicEtat, { qos: 0 }, (err) => {
            if (err) {
                console.error('Erreur lors de l’abonnement :', err);
            } else {
                console.log('Abonné au topic :', topicEtat);
            }
        });

        // Réception des messages du topic portail/status
        client.on('message', (topic, message) => {
            console.log('Message reçu :', topic, message.toString());
            document.getElementById("etat").innerHTML = "Etat : " + message.toString();
        });

        // Fonction appelée lors du clic sur le bouton d'ouverture
        function publication_OUVERTURE() {
            fetch("/publish/publish.php", {
                method: "POST"
            })
            .then(response => {
                if (response.ok) {
                    console.log("Commande envoyée au portail.");
                } else {
                    console.error("Erreur HTTP :", response.status);
                }
            })
            .catch(error => {
                console.error("Erreur réseau :", error);
            });
        }
    </script>
</body>
</html>
