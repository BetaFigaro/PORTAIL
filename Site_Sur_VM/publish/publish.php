<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : publish.php
// Ce script est déclenché lorsqu'un utilisateur connecté (admin ou normal)
// appuie sur le bouton "OUVERTURE DU PORTAIL". Il vérifie la session, la sécurité,
// enregistre l’action dans les logs, puis publie une commande MQTT "OUVERTURE".

session_start(); // Démarre la session PHP pour authentifier l'utilisateur

// Vérifie que l'utilisateur est connecté, sinon redirige
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Gestion du délai d'inactivité (déconnexion automatique après 15 min)
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();    // Supprime les variables de session
    session_destroy();  // Détruit la session
    header("Location: ../index.php"); // Retour à la connexion
    exit();
}

// Log de l’action "OUVERTURE"
require_once '../utils.php';
$pdo = getPDO(); // Connexion à la BDD
$username = $_SESSION['username'] ?? 'Visiteur';
$cle = $_SESSION['cle'] ?? null;
if (!$cle) {
    http_response_code(400); // Mauvaise requête si clé manquante
    exit;
}

$topic = "portail/$cle/action";

log_action($pdo, $username, 'OUVERTURE'); // Enregistre l'ouverture dans les logs

// Mise à jour de l'horodatage d'activité
$_SESSION['last_activity'] = time();

// Chargement de la librairie MQTT PHP
require("../lib/phpMQTT.php");

// Paramètres de connexion au broker MQTT
$server = '192.168.1.226';
$port = 1883; // Port MQTT standard
$username = 'portail'; // Identifiants MQTT
$password = 'Cu2kscd#bpF';
$client_id = 'phpMQTT_' . uniqid(); // ID unique pour chaque session

// Instanciation du client MQTT
$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

// Connexion au broker et publication du message
if ($mqtt->connect(true, NULL, $username, $password)) {
    // Publie "OUVERTURE" sur le topic portail/status
    $mqtt->publish("$topic", "OUVERTURE", $qos = 0, $retain = 1);
    $mqtt->close(); // Ferme la connexion MQTT proprement
    http_response_code(200); // Réponse OK
} else {
    // Échec de connexion au broker MQTT
    http_response_code(500); // Erreur serveur
}
?>
