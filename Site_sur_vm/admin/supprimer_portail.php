<?php
// Projet PORTAIL - Codé par Rafael
// Fichier : supprimer_portail.php
// Cette page permet à un administrateur connecté de supprimer complètement son portail,
// tous les comptes utilisateurs liés, et de réinitialiser le Raspberry à distance via MQTT.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../utils.php';

if (!isset($_SESSION['username']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$pdo = getPDO();
$nomAdmin = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT portail FROM USER_ADM WHERE Nom = ?");
$stmt->execute([$nomAdmin]);
$cle = $stmt->fetchColumn();

if (!$cle) {
    $message = "Impossible de récupérer la clé du portail.";
}
$cle_bdd = $cle; // On garde la CLE pour le log même après la suppression
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motdepasse = $_POST['motdepasse'] ?? '';

    // Vérifier le mot de passe de l'admin connecté
    $stmt = $pdo->prepare("SELECT password FROM USER_ADM WHERE Nom = ?");
    $stmt->execute([$nomAdmin]);
    $result = $stmt->fetch();

    // STOP si mot de passe incorrect
    if (!$result || !password_verify($motdepasse, $result['password'])) {
        $message = "❌ Mot de passe incorrect.";
    }
    // STOP si clé introuvable
    elseif (!$cle) {
        $message = "❌ Impossible de récupérer la clé du portail.";
    }
    // Si tout est bon, on exécute la suppression
    else {
        // Récupération des IDs des admins liés à la CLE (pour supprimer leurs utilisateurs)
        $stmt = $pdo->prepare("SELECT id FROM USER_ADM WHERE portail = ?");
        $stmt->execute([$cle]);
        $adminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Supprimer les utilisateurs normaux liés
        if ($adminIds) {
            $inClause = implode(',', array_fill(0, count($adminIds), '?'));
            $stmt = $pdo->prepare("DELETE FROM USER_NORM WHERE Admin IN ($inClause)");
            $stmt->execute($adminIds);
        }

        // Supprimer les admins liés à ce portail
        $stmt = $pdo->prepare("DELETE FROM USER_ADM WHERE portail = ?");
        $stmt->execute([$cle]);

        // Supprimer le portail
        $stmt = $pdo->prepare("DELETE FROM PORTAIL WHERE CLE = ?");
        $stmt->execute([$cle]);

        // Publication MQTT du message RESET
        require("../lib/phpMQTT.php");

        $server = '192.168.1.226';
        $port = 1883;
        $mqttUser = 'portail';
        $mqttPass = 'Cu2kscd#bpF';
        $client_id = 'phpMQTT_' . uniqid();
        $topic = "portail/$cle/action";

        $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $mqttUser, $mqttPass)) {
            $mqtt->publish($topic, "RESET", 0, 1); // Message spécial que le Raspberry comprendra
            $mqtt->close();
        }

        // Déconnecter et rediriger
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Suppression du Portail</title>
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <div class="center-content">
        <h1 style="color:white;">Suppression du portail</h1>
        <p style="color:white;">Cette action est <strong>irréversible</strong>. Tous les comptes liés seront supprimés.</p>

        <?php if ($message): ?>
            <p style="color:red;"><strong><?= htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <form method="post">
            <p style="color:white;">
                <label for="motdepasse">Confirmez avec votre mot de passe :</label><br>
                <input type="password" name="motdepasse" required/>
            </p>
            <button type="submit" class="bouton" style="background-color: #d32f2f;">Supprimer définitivement</button>
        </form>
    </div>
</body>
</html>
