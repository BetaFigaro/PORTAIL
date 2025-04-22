<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : veriftel.php
// Ce script est appelé en par le raspberry pour vérifier si un numéro de téléphone
// est autorisé à ouvrir le portail. Il consulte les bases USER_NORM et USER_ADM, vérifie les horaires, 
// puis publie une commande d'ouverture via MQTT si l'accès est autorisé.

require_once __DIR__ . '/utils.php'; // Inclusion des fonctions utiles

// Connexion à la base de données manuelle (au lieu de getPDO)
$host = 'localhost';
$dbname = 'PORTAIL';
$user = 'Rasping5939';
$pass = 'Puy1520PW5EJ';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // Création d'un objet PDO pour interagir avec la BDD
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    http_response_code(500); // Erreur serveur
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}

// Vérifie si un numéro de téléphone a été envoyé par POST
if (!isset($_POST['tel'])) {
    http_response_code(400); // Mauvaise requête
    echo "Numéro de téléphone non fourni.";
    exit;
}

$tel = $_POST['tel']; // Récupère le numéro envoyé

// Normalisation : si le numéro fait 9 chiffres et ne commence pas par 0, on ajoute un 0
if (strlen($tel) === 9 && $tel[0] !== '0') {
    $tel = '0' . $tel;
}

// Recherchez d'abord dans les utilisateurs normaux
$stmt = $pdo->prepare("SELECT * FROM USER_NORM WHERE tel = :tel");
$stmt->execute(['tel' => $tel]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$type = 'utilisateur normal';

// Si non trouvé, chercher dans les administrateurs
if (!$user) {
    $stmt = $pdo->prepare("SELECT * FROM USER_ADM WHERE tel = :tel");
    $stmt->execute(['tel' => $tel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $type = 'administrateur';
}

// Si un utilisateur correspondant est trouvé
if ($user) {
    $nomUtilisateur = $user['Nom']; // Récupère le nom
    date_default_timezone_set('Europe/Paris'); // Définit la timezone
    $heureActuelle = date("H:i"); // Heure actuelle au format HH:MM

    // Vérifie si l'accès est autorisé
    $autorise = $type === 'administrateur' || (
        isset($user['Heure_Debut']) &&
        $heureActuelle >= $user['Heure_Debut'] &&
        $heureActuelle <= $user['Heure_Fin']
    );

    if ($autorise) {
        // Récupération de la CLE du portail
        if ($type === 'utilisateur normal') {
            $stmtCle = $pdo->prepare("SELECT portail FROM USER_ADM WHERE id = ?");
            $stmtCle->execute([$user['Admin']]);
            $admin = $stmtCle->fetch(PDO::FETCH_ASSOC);
            $cle = $admin['portail'] ?? null;
        } else {
            $cle = $user['portail'] ?? null;
        }

        if (!$cle) {
            http_response_code(500);
            echo "Impossible de déterminer la CLE du portail.";
            exit;
        }

        $topic = "portail/$cle/action"; // Construction du topic dynamique

        // Inclusion de la librairie MQTT
        require("lib/phpMQTT.php");

        // Configuration MQTT
        $server = '192.168.1.226';
        $port = 1883;
        $username = 'portail';
        $password = 'Cu2kscd#bpF';
        $client_id = 'phpMQTT_' . uniqid();

        // Connexion MQTT
        $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            // Publication du message sur le topic dynamique
            $mqtt->publish($topic, "OUVERTURE", 0, 1);
            $mqtt->close();

            http_response_code(200); // Succès
            echo "Accès autorisé et message publié.";

            // Journalisation de l’action
            log_action($pdo, $nomUtilisateur, "Appel autorisé ($type) depuis le numéro : $tel");
        } else {
            // Échec de la connexion MQTT
            http_response_code(500);
            echo "Connexion MQTT échouée.";
        }
    } else {
        // Horaire non respecté
        http_response_code(403); // Interdit
        echo "Hors horaires autorisés.";
        log_action($pdo, $nomUtilisateur, "Appel refusé ($type - hors horaires) depuis le numéro : $tel");
    }
} else {
    // Aucun utilisateur correspondant
    http_response_code(404); // Non trouvé
    echo "Numéro non autorisé.";
    log_action($pdo, "Inconnu", "Appel refusé (numéro non autorisé) : $tel");
}
?>
