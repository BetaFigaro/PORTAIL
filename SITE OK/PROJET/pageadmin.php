<?php
session_start(); // Démarrer la session pour récupérer les informations de l'utilisateur

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header('Location: login.php');
    exit;
}

// Vérifier si l'utilisateur est administrateur via la variable "is_admin"
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Si l'utilisateur n'est pas administrateur, rediriger vers la page utilisateur par exemple
    header('Location: pageuser.php');
    exit;
}

// Gestion de la déconnexion
if (isset($_POST['logout'])) {
    // Supprimer toutes les variables de session
    session_unset();
    // Détruire la session
    session_destroy();
    // Rediriger vers la page de connexion après la déconnexion        
    header('Location: index.php');
    exit;
}

// Temps d'inactivité maximal (en secondes)
$timeout_duration = 900; // 15 minutes = 900 secondes

// Vérifier si la variable de session pour l'heure de dernière activité existe
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Si le temps d'inactivité est supérieur au délai, déconnecter l'utilisateur
    session_unset();
    session_destroy();
    header("Location: index.php"); // Rediriger vers la page d'accueil
    exit();
}

// Mettre à jour l'heure de dernière activité
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion du Portail</title>
    <!-- gestion accent -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- lien fichier css -->
    <link rel="stylesheet" type="text/css" href="../CSS/styles.css"> 
</head>
<body>
    <!-- Bouton Admin -->
    <a href="./admin/indexadmin.php" class="admin-button">Admin</a> 
    
    <div class="center-content">
        <font face="arial" size="7" color="WHITE">
        <b>
            GESTION DU PORTAIL
        </b>
        </font>
    </div>
    
    <div class="center-content">
        <font face="arial" size="6" color="WHITE">
        <br> 
        <!-- Bouton Ouverture portail -->
        <?php
        echo '<button class="bouton" onclick="publication_OUVERTURE()">OUVERTURE DU PORTAIL</button>';
        ?>
        <p id="etat" style="color: white;"> <br> Etat : FERMER</p>
        </font>
    </div>

    <!-- Bouton de déconnexion -->
    <form method="post" action="">
        <br>
        <button type="submit" name="logout" class="bouton-deco" style="background-color: #f44336;">Déconnexion</button> 
    </form>

    <!-- Script MQTT -->
    <script src="/JS/mqtt.min.js"></script>  
    <script type="text/javascript">
        // Vérifie si la bibliothèque mqtt est correctement chargée
        if (typeof mqtt === 'undefined') {
            console.error("La bibliothèque mqtt.min.js n'est pas chargée !");
        } else {
            //console.log("La bibliothèque mqtt.min.js est chargée :", mqtt);
        }

        // Options de connexion
        const connectUrl = 'ws://projet.betacorps.ovh:9001';
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
                payload: 'Connection Closed abnormally..! ',
                qos: 0,
                retain: false
            },
            username: 'portail',
            password: 'Cu2kscd#bpF'
        };

        // Connexion au broker MQTT
        const client = mqtt.connect(connectUrl, options);

        // Gestion des événements du client
        client.on('reconnect', (error) => {
            console.log('Reconnexion en cours :', error);
        });

        client.on('error', (error) => {
            console.error('Échec de la connexion :', error);
        });

        client.on('connect', () => {
            //console.log('Client connecté :', clientId);
            // Abonnement au topic
            client.subscribe('portail/status', { qos: 0 }, (err) => {
                if (!err) {
                    //console.log('Abonné au topic portail/status');
                } else {
                    console.error('Erreur lors de l’abonnement :', err);
                }
            });
        });

        client.on('message', (topic, message) => {
            console.log('Message reçu :', topic, message.toString());
            document.getElementById("etat").innerHTML = "Etat : " + message.toString();
        });

        // Fonctions de publication
        function publication_OUVERTURE() {
            client.publish('portail/status', 'OUVERTURE', { qos: 0, retain: false }, (err) => {
                if (err) {
                    console.error('Erreur lors de la publication :', err);
                } else {
                    console.log('Message publié : Portail en cours d ouverture');
                }
            });
        }
    </script>
</body>
</html>
