<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : publish_force_fermer.php
// Ce script permet à un utilisateur authentifié (admin) de forcer la fermeture du portail en publiant 
// une commande "FERMER" sur le broker MQTT. 
session_start(); // Démarre la session PHP pour accéder aux données utilisateur

// Vérifie si l'utilisateur est connecté, sinon le redirige
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Gestion de l'inactivité (déconnexion après 15 min)
$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset(); // Supprime les variables de session
    session_destroy(); // Détruit la session
    header("Location: ../index.php"); // Retour à la connexion
    exit();
}

// Journalise l'action dans la base de données
require_once '../utils.php';
$pdo = getPDO(); // Connexion BDD
$username = $_SESSION['username'] ?? 'Visiteur';
$cle = $_SESSION['cle'] ?? null;
if (!$cle) {
    http_response_code(400); // Mauvaise requête si clé manquante
    exit;
}

$topic = "portail/$cle/action";

log_action($pdo, $username, 'FORCAGE FERMERMETURE'); // Enregistre dans les logs

// Mise à jour de l'activité utilisateur
$_SESSION['last_activity'] = time();

// Inclusion de la bibliothèque MQTT PHP
require("../lib/phpMQTT.php");

// Paramètres de connexion au broker MQTT local
$server = '192.168.1.226';
$port = 1883;
$username = 'portail'; //  Identifiants du broker MQTT
$password = 'Cu2kscd#bpF';
$client_id = 'phpMQTT_' . uniqid(); // Génère un identifiant unique pour la session MQTT

// Création de l'instance MQTT
$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

// Connexion au broker MQTT
if ($mqtt->connect(true, NULL, $username, $password)) {
    // Publication de la commande "FERMER" sur le topic "portail/status"
    $mqtt->publish("$topic", "FERMER", 0, 1);
    $mqtt->close(); // Ferme proprement la connexion

    http_response_code(200); // Succès HTTP
    echo "Message publié : FERMER";
} else {
    // En cas d'échec de connexion au broker
    http_response_code(500);
    echo "Connexion au broker échouée";
}
