<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : utils.php
// Ce fichier contient les fonctions utilitaires réutilisables dans tout le projet :
// - Connexion sécurisée à la base de données
// - Récupération de l'adresse IP même derrière un proxy
// - Analyse du user-agent (système + navigateur)
// - Journalisation des actions utilisateur (logs)

date_default_timezone_set('Europe/Paris'); // Définit le fuseau horaire pour tous les scripts PHP

// Fonction de connexion à la base de données via PDO
function getPDO() {
    $host = 'localhost';
    $db   = 'PORTAIL';
    $user = 'Rasping5939';
    $pass = 'Puy1520PW5EJ';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // Chaîne de connexion (DSN)

    try {
        // Création de l'objet PDO avec des options de sécurité
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Renvoie les résultats sous forme associative
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Si erreur de connexion, stoppe le script avec un message clair
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Fonction pour récupérer l'adresse IP réelle, même si le serveur est derrière un proxy (Nginx Proxy Manager, etc.)
function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Peut contenir plusieurs IP séparées par des virgules => on prend la première
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }
    // Si pas de proxy, on prend l'adresse IP classique
    return $_SERVER['REMOTE_ADDR'];
}

// Fonction qui tente d'identifier l'appareil et le navigateur à partir du user-agent
function parse_user_agent($user_agent) {
    $device = 'Inconnu';
    $browser = 'Inconnu';

    // Détection du système d'exploitation
    if (stripos($user_agent, 'Windows') !== false) {
        $device = 'Windows';
    } elseif (stripos($user_agent, 'Android') !== false) {
        $device = 'Android';
    } elseif (stripos($user_agent, 'iPhone') !== false) {
        $device = 'iPhone';
    } elseif (stripos($user_agent, 'iPad') !== false) {
        $device = 'iPad';
    } elseif (stripos($user_agent, 'Mac OS X') !== false) {
        $device = 'macOS';
    } elseif (stripos($user_agent, 'Linux') !== false) {
        $device = 'Linux';
    }

    // Détection du navigateur
    if (stripos($user_agent, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (stripos($user_agent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (stripos($user_agent, 'Safari') !== false && stripos($user_agent, 'Chrome') === false) {
        $browser = 'Safari';
    } elseif (stripos($user_agent, 'Edge') !== false) {
        $browser = 'Edge';
    }

    return "$device – $browser"; // Format combiné "OS – Navigateur"
}

// Fonction de journalisation d'action dans la base de données LOGS
function log_action($pdo, $username, $action) {
    $ip = getUserIP();
    $timestamp = date('Y-m-d H:i:s');

    // Détection de user-agent
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $raw_agent = $_SERVER['HTTP_USER_AGENT'];
        $user_agent = parse_user_agent($raw_agent);
    } else {
        $user_agent = 'Appel système/API';
    }

    // Récupération de la CLE du portail liée à l'utilisateur
    $cle_portail = null;

    // Tenter de récupérer la CLE via USER_ADM
    $stmt = $pdo->prepare("SELECT portail FROM USER_ADM WHERE Nom = ?");
    $stmt->execute([$username]);
    if ($row = $stmt->fetch()) {
        $cle_portail = $row['portail'];
    } else {
        // Sinon, tenter via USER_NORM (via son admin)
        $stmt = $pdo->prepare("
            SELECT PORTAIL.CLE FROM USER_NORM 
            JOIN USER_ADM ON USER_NORM.Admin = USER_ADM.ID
            JOIN PORTAIL ON USER_ADM.portail = PORTAIL.CLE
            WHERE USER_NORM.Nom = ?
        ");
        $stmt->execute([$username]);
        if ($row = $stmt->fetch()) {
            $cle_portail = $row['CLE'];
        }
    }

    // Insertion dans les logs avec la CLE du portail
    $stmt = $pdo->prepare("
        INSERT INTO LOGS (username, ip_address, user_agent, action, timestamp, portail_cle)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$username, $ip, $user_agent, $action, $timestamp, $cle_portail]);
}