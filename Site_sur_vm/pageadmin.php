<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : pageadmin.php
// Cette page affiche l'interface d'administration principale du portail.
// Elle vérifie que l'utilisateur est bien connecté et admin, gère l'inactivité et permet de déclencher
// des actions via MQTT (ouverture/fermeture), ainsi que la déconnexion.

session_start(); // Démarre la session pour accéder aux variables de session
require_once 'utils.php'; // Inclusion des fonctions utilitaires

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header('Location: index.php'); // Redirige vers login si non connecté
    exit;
}

// Vérifie que l'utilisateur est bien administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: pageuser.php'); // Redirige vers interface utilisateur si pas admin
    exit;
}

// Déconnexion si bouton "logout" cliqué
if (isset($_POST['logout'])) {
    session_unset();    // Supprime toutes les variables de session
    session_destroy();  // Détruit la session
    header('Location: index.php'); // Retour à la page de connexion
    exit;
}

// Gère l'expiration automatique après 15 minutes d'inactivité
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    header("Location: index.php"); // Retour à la page de connexion
    exit();
}

// Met à jour l'horodatage de la dernière activité
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion du Portail</title>
    <meta charset="UTF-8"> <!-- Support des accents -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> <!-- Lien vers le CSS -->
</head>
<body>

    <!-- Boutons d accès rapide admin -->
    <div class="admin-buttons">
        <a href="./admin/indexadmin.php" class="admin-button">Interface Administrateur</a>
        <button class="admin-button red" onclick="publication_FORCAGE_FERMETURE()">Forcer FERMETURE</button>
    </div>

    <!-- Titre principal -->
    <div class="center-content">
        <font face="arial" size="7" color="WHITE"><b>GESTION DU PORTAIL</b></font>
    </div>

    <!-- Bouton d'ouverture du portail + affichage de l'état -->
    <div class="center-content">
        <font face="arial" size="6" color="WHITE">
        <br>
        <?php
        // Génère le bouton d'ouverture
        echo '<button class="bouton" onclick="publication_OUVERTURE()">OUVERTURE DU PORTAIL</button>';
        ?>
        <p id="etat" style="color: white;"><br> Etat : FERMER</p>
        </font>
    </div>

    <!-- Formulaire de déconnexion -->
    <form method="post" action="">
        <br>
        <button type="submit" name="logout" class="bouton-deco" style="background-color: #f44336;">Déconnexion</button>
    </form>

    <!-- Scripts de gestion MQTT -->
    <script>
    const CLE = "<?= $_SESSION['cle'] ?? '' ?>";
    </script>
    <script src="/JS/mqtt.min.js"></script>
    <script type="text/javascript">
        // Vérifie si mqtt.min.js est bien chargé
        if (typeof mqtt === 'undefined') {
            console.error("La bibliothèque mqtt.min.js n'est pas chargée !");
        }

        // Configuration du client MQTT
        const connectUrl = 'wss://mqtt.projet.betacorps.ovh';
        const clientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8);

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

        // Connexion au broker MQTT
        const client = mqtt.connect(connectUrl, options);

        // Reconnexion automatique
        client.on('reconnect', (error) => {
            console.log('Reconnexion en cours :', error);
        });

        // Gestion des erreurs de connexion
        client.on('error', (error) => {
            console.error('Échec de la connexion :', error);
        });

        // Une fois connecté, s'abonne au topic pour suivre l'état du portail
        const topicEtat = `portail/${CLE}/action`;

        client.subscribe(topicEtat, { qos: 0 }, (err) => {
            if (err) {
                console.error('Erreur lors de l’abonnement :', err);
            } else {
                console.log('Abonné au topic :', topicEtat);
            }
        });

        // Affiche l'état du portail reçu via MQTT
        client.on('message', (topic, message) => {
            document.getElementById("etat").innerHTML = "Etat : " + message.toString();
        });

        // Fonction pour publier une commande d'ouverture
        function publication_OUVERTURE() {
            fetch("/publish/publish.php", {
                method: "POST",
                credentials: "same-origin"
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

        // Fonction pour forcer la fermeture du portail
        function publication_FORCAGE_FERMETURE() {
            if (!confirm("⚠️ Es-tu sûr de vouloir forcer la fermeture du portail ?")) return;

            fetch("/publish/publish_force_fermer.php", {
                method: "POST",
                credentials: "same-origin"
            })
            .then(response => response.text().then(txt => {
                if (!response.ok) {
                    console.error("Erreur HTTP :", response.status);
                } else {
                    alert("Commande envoyée : Forçage de fermeture");
                }
            }))
            .catch(error => {
                console.error("Erreur réseau :", error);
                alert("Erreur lors de l'envoi !");
            });
        }
    </script>
</body>
</html>
